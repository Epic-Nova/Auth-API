<?php

namespace App\Traits\Deprecated;

use Illuminate\Support\Facades\Log;

trait ParametersTrait
{
    use ErrorHandlerTrait;

    /**
     * Performs a type and value check on the given parameter on the current request
     *
     * @param  string  $Parameter  The Parameter to check
     * @param  string  $Type  The type of the parameter
     * @return bool True if the Parameter is set and the Type is correct
     */
    public function PerformParameterTypeAndValueCheck(string $Parameter, string $Type, array $Parameters = []): bool
    {
        Log::debug('PerformParameterTypeAndValueCheck: Performing type and value check on parameter: '.$Parameter.' with type: '.$Type);

        if (empty($Parameters)) {
            $Parameters = request()->all();
        }

        if (! is_numeric($Parameters[$Parameter]) && $Parameters[$Parameter] == null || ($Parameters[$Parameter] == '')) {
            $this->HandleError('errors.com.epicnova.request.parameter_null_or_empty', ['ParameterName' => $Parameter]);
        }

        Log::debug('PerformParameterTypeAndValueCheck: Parameter: '.$Parameter.' is set and not empty');

        if (isset($Parameters[$Parameter]) && gettype($Parameters[$Parameter]) == $Type) {
            Log::debug('PerformParameterTypeAndValueCheck: Parameter: '.$Parameter.' is of type: '.$Type);

            return true;
        }
        Log::debug('PerformParameterTypeAndValueCheck: Parameter: '.$Parameter.' is not of type: '.$Type);
        $this->HandleError('errors.com.epicnova.request.parameter_type_mismatch', ['ParameterName' => $Parameter, 'ParameterType' => $Type]);

        return false;
    }

    /**
     * Takes the parameters from the request and checks if they are set and if they are of the correct type
     *
     * @param  array  $RequiredParameters  Parameters that are required to be present in the request
     * @param  array  $OptionalParameters  Parameters that are optional to be present in the request
     * @param  bool  $IgnoreNotRequiredParameters  If true, the function will not throw an error if a parameter is not required
     */
    public function CheckParameters(array $RequiredParameters, array $OptionalParameters = [], bool $IgnoreNotRequiredParameters = false, array $Parameters = []): void
    {
        Log::debug('CheckParameters: Checking parameters');

        if (empty($Parameters)) {
            $Parameters = request()->all();
        }
        $MissingRequiredParameters = [];

        foreach (array_keys($RequiredParameters) as $RequiredParameter) {
            if (! in_array($RequiredParameter, array_keys($Parameters))) {
                Log::debug('CheckParameters: Required parameter: '.$RequiredParameter.' is missing');
                $MissingRequiredParameters[] = $RequiredParameter;
            }
        }

        if (count($MissingRequiredParameters) > 1) {
            Log::debug('CheckParameters: More than one required parameter is missing');
            $this->HandleError('errors.com.epicnova.request.multiple_parameters_missing', ['ParameterNames' => implode(', ', $MissingRequiredParameters)]);
        }

        Log::debug('CheckParameters: Checking required parameters');
        foreach (array_keys($RequiredParameters) as $RequiredParameter) {
            if (! $this->HasParameter($RequiredParameter, $Parameters)) {
                Log::debug('CheckParameters: Required parameter: '.$RequiredParameter.' is missing');
                $this->HandleError('errors.com.epicnova.request.parameter_missing', ['ParameterName' => $RequiredParameter]);
            }
            $this->PerformParameterTypeAndValueCheck($RequiredParameter, $RequiredParameters[$RequiredParameter], $Parameters);
        }

        Log::debug('CheckParameters: Checking optional parameters');
        foreach (request()->keys() as $Parameter) {
            if (! $IgnoreNotRequiredParameters && ! in_array($Parameter, array_keys($RequiredParameters)) && ! in_array($Parameter, array_keys($OptionalParameters))) {
                Log::debug('CheckParameters: Parameter: '.$Parameter.' is not allowed');
                $this->HandleError('errors.com.epicnova.request.parameter_not_allowed', ['ParameterName' => $Parameter]);
            } elseif (in_array($Parameter, array_keys($OptionalParameters))) {
                Log::debug('CheckParameters: Parameter: '.$Parameter.' is optional');
                $this->PerformParameterTypeAndValueCheck($Parameter, $OptionalParameters[$Parameter], $Parameters);
            }
        }

        Log::debug('CheckParameters: Parameters are valid');
    }

    public function CheckArrayParameters(array $Array, array $RequiredParameters): void
    {
        Log::debug('CheckArrayParameters: Checking parameters');

        foreach ($Array as $ArrayParameter) {
            Log::debug('CheckArrayParameters: Checking parameter: '.array_search($ArrayParameter, $Array));

            if ($this->GetJSONType(json_encode($ArrayParameter)) == 'array') {
                Log::debug('CheckArrayParameters: Checking array parameter: '.json_encode($ArrayParameter));
                $this->CheckArrayParameters($ArrayParameter, $RequiredParameters);
            } elseif ($this->GetJSONType(json_encode($ArrayParameter)) == 'object') {
                Log::debug('CheckArrayParameters: Checking object parameter: '.json_encode($ArrayParameter).' with type: '.gettype($ArrayParameter));
                $this->CheckObjectParameters($ArrayParameter, $RequiredParameters);
            } else {
                Log::debug('CheckArrayParameters: Checking parameter '.array_search($ArrayParameter, $Array).' with type '.gettype($ArrayParameter));
                if (! is_numeric($ArrayParameter) && $ArrayParameter == null || ($ArrayParameter == '')) {
                    Log::error('CheckArrayParameters: Parameter is null or empty');
                    $this->HandleError('errors.com.epicnova.request.parameter_null_or_empty', ['ParameterName' => array_search($ArrayParameter, $Array)]);
                }
            }
        }
    }

    public function CheckObjectParameters(array $Object, array $RequiredParameters): void
    {
        Log::debug('CheckObjectParameters: Checking parameters');
        foreach ($Object as $Parameter) {
            Log::debug('CheckObjectParameters: Checking parameter: '.json_encode($Parameter).' with type: '.gettype($Parameter));

            if ($this->GetJSONType(json_encode($Parameter)) == 'array') {
                Log::debug('CheckObjectParameters: Checking array parameter');
                $this->CheckArrayParameters($Parameter, $RequiredParameters);
            } elseif ($this->GetJSONType(json_encode($Parameter)) == 'object') {
                Log::debug('CheckObjectParameters: Checking object parameter');
                $this->CheckObjectParameters($Parameter, $RequiredParameters);
            }
        }
        $this->CheckParameters($RequiredParameters, [], true, $Object);
    }

    /**
     * Checks if the given parameter exists in the request
     *
     * @param  string  $Parameter  The Parameter to check
     * @return bool True if the request has the given parameter
     */
    public function HasParameter(string $Parameter, array $Parameters = []): bool
    {
        if (empty($Parameters)) {
            $Parameters = request()->all();
        }

        return array_key_exists($Parameter, $Parameters);
    }

    /**
     * Returns the value of one of the parameters in the request, both parameters cannot be present at the same time
     *
     * @param  string  $FirstParameterKey  The first parameter to check
     * @param  string  $SecondParameterKey  The second parameter to check
     * @param  mixed  $FirstParameter  The reference to the variable that will contain the value of the first parameter
     * @param  mixed  $SecondParameter  The reference to the variable that will contain the value of the second parameter
     */
    public function GetFirstOrSecondParameter(string $FirstParameterKey, string $SecondParameterKey, mixed &$FirstParameter, mixed &$SecondParameter): void
    {
        Log::debug('GetFirstOrSecondParameter: Getting first or second parameter');

        $HasFirstParameter = $this->HasParameter($FirstParameterKey);
        $HasSecondParameter = $this->HasParameter($SecondParameterKey);

        if ($HasFirstParameter && $HasSecondParameter) {
            Log::debug('GetFirstOrSecondParameter: Both parameters are present');
            $this->HandleError('errors.com.epicnova.request.both_parameters_present', ['ParameterNames' => $FirstParameterKey.' and '.$SecondParameterKey]);
        } elseif ($HasFirstParameter) {
            Log::debug('GetFirstOrSecondParameter: First parameter is present');
            $FirstParameter = request() - $FirstParameterKey;
        } elseif ($HasSecondParameter) {
            Log::debug('GetFirstOrSecondParameter: Second parameter is present');
            $SecondParameter = request()->$SecondParameterKey;
        } else {
            Log::debug('GetFirstOrSecondParameter: Neither parameter is present');
            $this->HandleError('errors.com.epicnova.request.parameter_missing', ['ParameterName' => $FirstParameterKey.' or '.$SecondParameterKey]);
        }
    }

    /**
     * Returns the type of JSON parameter
     *
     * @param  string  $JSON  The JSON Parameter to check
     * @return string Gets the Type of JSON parameter
     */
    public function GetJSONType(string $JSON): string
    {
        $JSON = ltrim($JSON);

        if (str_starts_with($JSON, '{')) {
            return 'object';
        } elseif (str_starts_with($JSON, '[')) {
            return 'array';
        }

        return false;
    }
}
