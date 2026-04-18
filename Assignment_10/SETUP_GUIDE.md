# Task Management System - Setup Guide

## ✅ Project Status: READY TO RUN

### System Requirements Verified
- ✅ **Java**: 23.0.2 (Compatible with Java 17 requirement)
- ✅ **Maven**: 3.9.14
- ✅ **MySQL**: 8.0.44
- ✅ **Project Build**: SUCCESS

---

## 🏗️ Project Architecture

### Technology Stack
| Component | Technology |
|-----------|------------|
| Framework | Spring Boot 4.0.5 |
| Language | Java 17 |
| Build Tool | Maven |
| Database | MySQL 8.0.44 |
| ORM | Hibernate/JPA |
| Template Engine | Thymeleaf |
| Frontend | HTML5, CSS3 |

### Project Structure
```
src/main/
├── java/com/example/task_management/
│   ├── TaskManagementApplication.java          # Spring Boot entry point
│   ├── controller/
│   │   ├── DashboardController.java             # Dashboard analytics
│   │   ├── EmployeeController.java              # Employee CRUD
│   │   └── TaskController.java                  # Task CRUD
│   ├── model/
│   │   ├── Employee.java                        # Employee entity
│   │   └── Task.java                            # Task entity
│   └── repository/
│       ├── EmployeeRepository.java              # Employee JPA repo
│       └── TaskRepository.java                  # Task JPA repo
├── resources/
│   ├── application.properties                   # Application config
│   ├── static/css/
│   │   └── style.css                           # Styling
│   └── templates/
│       ├── index.html                           # Dashboard
│       ├── employees/
│       │   ├── list.html
│       │   └── form.html
│       └── tasks/
│           ├── list.html
│           └── form.html
```

---

## 🚀 How to Run the Application

### Option 1: Run using Maven (Recommended)
```bash
cd c:\Users\vaish\OneDrive\Desktop\WTL\Assignment_10
mvn spring-boot:run
```

### Option 2: Run the JAR directly
```bash
cd c:\Users\vaish\OneDrive\Desktop\WTL\Assignment_10
java -jar target/task-management-0.0.1-SNAPSHOT.jar
```

### Option 3: Run in IDE
- Right-click `TaskManagementApplication.java`
- Select `Run As` → `Spring Boot App`

---

## 🌐 Access the Application

Once running, open your browser and navigate to:
```
http://localhost:8080
```

### Default Pages
| Page | URL | Description |
|------|-----|-------------|
| Dashboard | http://localhost:8080 | Analytics & overview |
| Employees | http://localhost:8080/employees | Manage employees |
| Tasks | http://localhost:8080/tasks | Manage tasks |

---

## 💾 Database Configuration

### Connection Details
- **URL**: jdbc:mysql://localhost:3306/task_management
- **Username**: root
- **Password**: root
- **Driver**: MySQL Connector/J (com.mysql.cj.jdbc.Driver)
- **Dialect**: MySQLDialect
- **DDL Mode**: auto (Hibernate creates tables on startup)

### Database Schema (Auto-created)

#### employees table
```sql
CREATE TABLE employees (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(255) NOT NULL,
  email VARCHAR(255) NOT NULL,
  department VARCHAR(255) NOT NULL
);
```

#### tasks table
```sql
CREATE TABLE tasks (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  description VARCHAR(255) NOT NULL,
  task_type VARCHAR(255) NOT NULL,
  due_date DATE NOT NULL,
  status VARCHAR(255) NOT NULL,
  employee_id BIGINT NOT NULL,
  FOREIGN KEY (employee_id) REFERENCES employees(id)
);
```

---

## 🧪 Build & Test

### Full Build with Tests
```bash
mvn clean install
```

### Run Tests Only
```bash
mvn test
```

### Build Output
- **Build Status**: ✅ SUCCESS
- **Tests Passed**: 1/1
- **JAR Location**: `target/task-management-0.0.1-SNAPSHOT.jar`

---

## 📋 Features

### Employee Management
- ✅ View all employees
- ✅ Add new employee
- ✅ Update employee details
- ✅ Delete employee (with cascade delete of tasks)
- ✅ Email validation
- ✅ Department assignment

### Task Management
- ✅ View all tasks
- ✅ Create new task
- ✅ Assign task to employee
- ✅ Update task status (PENDING, COMPLETED)
- ✅ Delete task
- ✅ Task filtering by status
- ✅ Task filtering by employee

### Dashboard
- ✅ Total employees count
- ✅ Total tasks count
- ✅ Pending tasks count
- ✅ Completed tasks count
- ✅ Recent tasks (top 5)
- ✅ Kanban board view
- ✅ Analytics overview

---

## 🔧 Configuration

### application.properties
```properties
spring.application.name=task-management

# Database
spring.datasource.url=jdbc:mysql://localhost:3306/task_management?createDatabaseIfNotExist=true&useSSL=false&allowPublicKeyRetrieval=true
spring.datasource.username=root
spring.datasource.password=root
spring.datasource.driver-class-name=com.mysql.cj.jdbc.Driver

# Hibernate/JPA
spring.jpa.database-platform=org.hibernate.dialect.MySQLDialect
spring.jpa.hibernate.ddl-auto=update
spring.jpa.show-sql=true
```

---

## 🐛 Troubleshooting

### MySQL Connection Failed
1. Ensure MySQL server is running
2. Verify credentials (username: root, password: root)
3. Check if database `task_management` exists
4. Verify MySQL is listening on port 3306

### Port 8080 Already in Use
```bash
# Change server port in application.properties
server.port=8081
```

### Maven Build Fails
```bash
# Clean Maven cache
mvn clean install -U
```

### Tables Not Created
- Hibernate should create tables automatically with `ddl-auto=update`
- Check MySQL logs for errors
- Ensure database `task_management` exists

---

## 📦 Dependencies

### Core Dependencies
- `spring-boot-starter-data-jpa`: Database access
- `spring-boot-starter-thymeleaf`: Template engine
- `spring-boot-starter-web`: Web framework
- `spring-boot-starter-validation`: Bean validation
- `mysql-connector-j`: MySQL driver
- `h2`: H2 console support

### Test Dependencies
- `spring-boot-starter-test`: Testing framework

---

## 📝 Build Information

- **Project Name**: task-management
- **Version**: 0.0.1-SNAPSHOT
- **Group ID**: com.example
- **Artifact ID**: task-management
- **Java Version**: 17
- **Spring Boot Version**: 4.0.5
- **Maven Version**: 3.9.14

---

## ✨ Next Steps

1. **Run the application**
   ```bash
   mvn spring-boot:run
   ```

2. **Open in browser**
   ```
   http://localhost:8080
   ```

3. **Add employees** and **Create tasks** through the UI

4. **Monitor the application** using console logs

---

## 📞 Support

If you encounter any issues:
1. Check the console logs for error messages
2. Verify MySQL is running
3. Ensure port 8080 is available
4. Clear Maven cache: `mvn clean`
5. Rebuild: `mvn install`

---

**Last Updated**: April 18, 2026  
**Status**: ✅ Ready for Production
