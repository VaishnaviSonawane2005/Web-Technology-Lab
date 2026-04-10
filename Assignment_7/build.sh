#!/bin/bash

set -e

echo "======================================"
echo "Bookstore Application Build"
echo "======================================"
echo

if [ ! -d "src" ]; then
    echo "[X] src directory not found."
    exit 1
fi

if [ -z "$CATALINA_HOME" ]; then
    echo "[X] Set CATALINA_HOME to your Tomcat installation directory first."
    exit 1
fi

if [ ! -d "$CATALINA_HOME/lib" ]; then
    echo "[X] CATALINA_HOME is set to $CATALINA_HOME but that folder does not contain a lib directory."
    exit 1
fi

SERVLET_JAR=""
for jar in "$CATALINA_HOME"/lib/servlet-api.jar "$CATALINA_HOME"/lib/jakarta.servlet-api*.jar; do
    if [ -f "$jar" ]; then
        SERVLET_JAR="$jar"
        break
    fi
done

if [ -z "$SERVLET_JAR" ]; then
    echo "[X] Servlet API JAR not found inside $CATALINA_HOME/lib"
    exit 1
fi

MYSQL_JAR=""
for jar in WebContent/WEB-INF/lib/mysql-connector-j*.jar "$CATALINA_HOME"/lib/mysql-connector-j*.jar; do
    if [ -f "$jar" ]; then
        MYSQL_JAR="$jar"
        break
    fi
done

if [ -z "$MYSQL_JAR" ]; then
    echo "[X] MySQL Connector/J JAR not found in WebContent/WEB-INF/lib or $CATALINA_HOME/lib"
    exit 1
fi

echo "[*] Preparing build folders..."
rm -rf build/classes build/bookstore
mkdir -p build/classes build/bookstore

echo "[*] Compiling Java sources..."
javac --release 17 -d build/classes -cp "$SERVLET_JAR:$MYSQL_JAR" src/com/bookstore/*.java

echo "[*] Copying web resources..."
cp -r WebContent/. build/bookstore/

echo "[*] Copying compiled classes..."
mkdir -p build/bookstore/WEB-INF/classes
cp -r build/classes/. build/bookstore/WEB-INF/classes/

echo "[*] Packaging WAR..."
(
    cd build/bookstore
    jar -cf ../bookstore.war .
)

echo
echo "[OK] WAR created at build/bookstore.war"
echo "[OK] Deploy it to $CATALINA_HOME/webapps/"
echo "[OK] Open http://localhost:8080/bookstore/"
