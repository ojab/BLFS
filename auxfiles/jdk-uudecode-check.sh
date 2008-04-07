#! /bin/sh
# $LastChangedBy$
# $Date$

${JAVA_HOME}/bin/javac -version 2>&1 | head -1 | grep -q 1\.6
if [ $? -eq 1 ]; then
     echo; echo "JDK/uudecode combination is okay, proceed with the installation :-)"; echo
else
    uudecode --version 2>&1 | head -1 | grep -q GMime
    if [ $? -eq 1 ]; then
        echo; echo "JDK/uudecode combination is okay, proceed with the installation :-)"; echo
    else
        echo; echo "JDK/uudecode combination is bad, modify the ./configure command as shown below:"; echo
        echo 'uudecode="no" ../dist/configure --(use the parameters shown in the book)'; echo
    fi
fi

