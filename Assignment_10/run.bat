@echo off
REM Task Management Application - Quick Start Script
REM This script builds and runs the Spring Boot application

ECHO.
ECHO ========================================
ECHO   Task Management System - Quick Start
ECHO ========================================
ECHO.

REM Check if Maven is installed
mvn --version >nul 2>&1
IF ERRORLEVEL 1 (
    ECHO ERROR: Maven is not installed or not in PATH
    ECHO Please install Maven and add it to your PATH
    EXIT /B 1
)

REM Check if Java is installed
java -version >nul 2>&1
IF ERRORLEVEL 1 (
    ECHO ERROR: Java is not installed or not in PATH
    ECHO Please install Java 17 or higher
    EXIT /B 1
)

ECHO [1/3] Cleaning previous builds...
mvn clean >nul 2>&1

ECHO [2/3] Building the project...
mvn install -q
IF ERRORLEVEL 1 (
    ECHO ERROR: Build failed
    EXIT /B 1
)

ECHO [3/3] Starting the application...
ECHO.
ECHO Starting Spring Boot application...
ECHO Application will be available at: http://localhost:8080
ECHO.
ECHO Press Ctrl+C to stop the application.
ECHO.

mvn spring-boot:run

PAUSE
