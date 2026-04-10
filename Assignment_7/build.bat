@echo off
setlocal enabledelayedexpansion

echo ======================================
echo Bookstore Application Build
echo ======================================
echo.

if not exist "src" (
    echo [X] src directory not found.
    exit /b 1
)

if not defined CATALINA_HOME (
    echo [X] Set CATALINA_HOME to your Tomcat installation directory first.
    exit /b 1
)

if not exist "%CATALINA_HOME%\lib" (
    echo [X] CATALINA_HOME is set to %CATALINA_HOME% but that folder does not contain a lib directory.
    exit /b 1
)

set "SERVLET_JAR="
for %%F in ("%CATALINA_HOME%\lib\servlet-api.jar" "%CATALINA_HOME%\lib\jakarta.servlet-api*.jar") do (
    if exist "%%~fF" (
        set "SERVLET_JAR=%%~fF"
        goto :servlet_found
    )
)

:servlet_found
if not defined SERVLET_JAR (
    echo [X] Servlet API JAR not found inside %CATALINA_HOME%\lib
    exit /b 1
)

set "MYSQL_JAR="
for %%F in ("WebContent\WEB-INF\lib\mysql-connector-j*.jar") do (
    if exist "%%~fF" (
        set "MYSQL_JAR=%%~fF"
        goto :mysql_found
    )
)
for %%F in ("%CATALINA_HOME%\lib\mysql-connector-j*.jar") do (
    if exist "%%~fF" (
        set "MYSQL_JAR=%%~fF"
        goto :mysql_found
    )
)

:mysql_found
if not defined MYSQL_JAR (
    echo [X] MySQL Connector/J JAR not found in WebContent\WEB-INF\lib or %CATALINA_HOME%\lib
    exit /b 1
)

echo [*] Preparing build folders...
if exist "build\classes" rmdir /s /q build\classes
if exist "build\bookstore" rmdir /s /q build\bookstore
mkdir build\classes
mkdir build\bookstore

echo [*] Compiling Java sources...
javac --release 17 -d build\classes -cp "%SERVLET_JAR%;%MYSQL_JAR%" src\com\bookstore\*.java
if errorlevel 1 (
    echo [X] Compilation failed.
    exit /b 1
)

echo [*] Copying web resources...
xcopy /E /I /Y WebContent build\bookstore >nul

echo [*] Copying compiled classes...
if not exist "build\bookstore\WEB-INF\classes" mkdir build\bookstore\WEB-INF\classes
xcopy /E /I /Y build\classes build\bookstore\WEB-INF\classes >nul

echo [*] Packaging WAR...
cd build\bookstore
jar -cf ..\bookstore.war .
cd ..\..

echo.
echo [OK] WAR created at build\bookstore.war
echo [OK] Deploy it to %CATALINA_HOME%\webapps\
echo [OK] Open http://localhost:8080/bookstore/
