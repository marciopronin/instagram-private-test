#!/bin/bash
echo "##########################"
echo "      IG PROFILER         "
echo "##########################"
echo -e "\n"

if [ ! -d "output-apktool" ]; then
    if [ $# -eq 0 ]; then
        echo "Usage: ./locate.sh <ig.apk>"
        exit 1
    fi
    if [ ! -f "$1" ]; then
        echo "$1 does not exist."
        echo "Usage: ./locate.sh <ig.apk>"
        exit 1
    fi
    apktool d "$1" -o output-apktool
else
    echo -e "Folder 'output-apktool' detected. Processing it...\n"
fi
class=$(grep -iRlH 'X-Bloks-Version-Id' ./output-apktool)
bloks=$(egrep -oh "(\w{64})" $class)
version=$(egrep -oh "versionName: (\w+\.\w+\.\w+\.\w+\.\w+)" ./output-apktool/apktool.yml)
versioncode=$(egrep -oh "versionCode: '(\d+)'" ./output-apktool/apktool.yml)
classcap=$(grep -iRlH 'X-IG-Capabilities' ./output-apktool)
cap=$(egrep -oh "(\w+=)" $classcap)

echo "$version"
echo "$versioncode"

echo "BLOKS CLASS: $class"
echo "BLOKS_ID: $bloks"
echo "CAPABILITIES: $cap"