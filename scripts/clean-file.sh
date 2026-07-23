#!/bin/bash

# Check if a target path was provided
if [ -z "$1" ]; then
    echo "Usage: $0 <path-to-file-or-folder>"
    exit 1
fi

TARGET_PATH="$1"

clean_file() {
    local file="$1"
    echo "Processing: $file"

    # 1. Remove UTF-8 BOM if present
    sed -i '1s/^\xEF\xBB\xBF//' "$file"

    # 2. Strip sneaky Non-Breaking Spaces (C2 A0) and trailing whitespaces before <?php
    # This ensures <?php is the absolute first thing on line 1
    awk '
    BEGIN { found=0 }
    {
        if (!found) {
            # Clean non-breaking spaces and spaces from the line
            gsub(/\x1b/,"",$0);
            gsub(/\xc2\xa0/," ",$0);
            gsub(/[ \t\r\n]/,"",$0);

            if ($0 ~ /^<\?php/) {
                # Found the opening tag, rebuild clean from here
                print "<?php";
                found=1;
                next;
            }
        } else {
            print $0;
        }
    }
    ' "$file" > "${file}.tmp" && mv "${file}.tmp" "$file"
}

if [ -f "$TARGET_PATH" ]; then
    # Clean a single file
    clean_file "$TARGET_PATH"
    echo "✅ Single file cleaned successfully."
elif [ -d "$TARGET_PATH" ]; then
    # Clean a folder recursively
    export -f clean_file
    find "$TARGET_PATH" -type f -name "*.php" | while read -r php_file; do
        clean_file "$php_file"
    done
    echo "✅ All PHP files in directory processed safely."
else
    echo "❌ Error: Target path '$TARGET_PATH' is not a valid file or directory."
    exit 1
fi
