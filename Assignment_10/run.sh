#!/bin/bash
# Task Management Application - Quick Start Script (Linux/Mac)
# This script builds and runs the Spring Boot application

echo ""
echo "========================================"
echo "  Task Management System - Quick Start"
echo "========================================"
echo ""

# Check if Maven is installed
if ! command -v mvn &> /dev/null; then
    echo "ERROR: Maven is not installed or not in PATH"
    echo "Please install Maven and add it to your PATH"
    exit 1
fi

# Check if Java is installed
if ! command -v java &> /dev/null; then
    echo "ERROR: Java is not installed or not in PATH"
    echo "Please install Java 17 or higher"
    exit 1
fi

echo "[1/3] Cleaning previous builds..."
mvn clean > /dev/null 2>&1

echo "[2/3] Building the project..."
mvn install -q
if [ $? -ne 0 ]; then
    echo "ERROR: Build failed"
    exit 1
fi

echo "[3/3] Starting the application..."
echo ""
echo "Starting Spring Boot application..."
echo "Application will be available at: http://localhost:8080"
echo ""
echo "Press Ctrl+C to stop the application."
echo ""

mvn spring-boot:run
