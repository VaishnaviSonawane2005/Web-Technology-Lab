# Task Management System - Configuration Reference

## 🔧 Application Configuration Options

### application.properties (Current Configuration)
Located at: `src/main/resources/application.properties`

```properties
# Application Name
spring.application.name=task-management

# MySQL Database Configuration
spring.datasource.url=jdbc:mysql://localhost:3306/task_management?createDatabaseIfNotExist=true&useSSL=false&allowPublicKeyRetrieval=true
spring.datasource.username=root
spring.datasource.password=root
spring.datasource.driver-class-name=com.mysql.cj.jdbc.Driver

# Hibernate/JPA Configuration
spring.jpa.database-platform=org.hibernate.dialect.MySQLDialect
spring.jpa.hibernate.ddl-auto=update
spring.jpa.show-sql=true
```

---

## 🔌 Database Configuration

### Current Setup
- **Database**: task_management
- **Host**: localhost
- **Port**: 3306
- **Username**: root
- **Password**: root
- **Dialect**: MySQLDialect
- **DDL Mode**: update (creates tables if they don't exist, updates schema)

### To Change Database Connection

Create a new file or modify `application-production.properties`:

```properties
# Production Database
spring.datasource.url=jdbc:mysql://your-db-host:3306/task_management
spring.datasource.username=your_username
spring.datasource.password=your_password
```

Run with profile:
```bash
mvn spring-boot:run -Dspring-boot.run.arguments="--spring.profiles.active=production"
```

---

## 🌐 Server Configuration

### Change Server Port

Add to `application.properties`:
```properties
# Server Port (default: 8080)
server.port=9090
```

### Enable HTTPS

Add to `application.properties`:
```properties
server.ssl.key-store=classpath:keystore.p12
server.ssl.key-store-password=your-password
server.ssl.key-store-type=PKCS12
```

### Context Path

Add to `application.properties`:
```properties
server.servlet.context-path=/taskapp
```
(Application would then be at: http://localhost:8080/taskapp)

---

## 📊 Logging Configuration

### Enable Debug Logging

Add to `application.properties`:
```properties
logging.level.root=INFO
logging.level.com.example.task_management=DEBUG
logging.level.org.springframework.web=DEBUG
logging.level.org.hibernate.SQL=DEBUG
logging.level.org.hibernate.type.descriptor.sql.BasicBinder=TRACE
```

### Log File Output

Add to `application.properties`:
```properties
logging.file.name=logs/application.log
logging.file.max-size=10MB
logging.file.max-history=10
```

---

## 📈 JPA/Hibernate Configuration

### DDL Auto Options
```properties
# Options: none, validate, update, create, create-drop
spring.jpa.hibernate.ddl-auto=update
```

| Option | Behavior |
|--------|----------|
| `none` | No action (use manual migrations) |
| `validate` | Only validate schema matches entities |
| `update` | Update schema (DEFAULT for development) |
| `create` | Create schema, drop on app stop |
| `create-drop` | Create schema, drop on app shutdown |

### SQL Formatting

```properties
# Show SQL in logs
spring.jpa.show-sql=true

# Format SQL nicely
spring.jpa.properties.hibernate.format_sql=true

# Show SQL comments
spring.jpa.properties.hibernate.use_sql_comments=true
```

---

## 🔐 Security Configuration

### CSRF Protection
```properties
# Disable for API testing
spring.security.csrf.enabled=false
```

### CORS Configuration
Create a new configuration class if needed for cross-origin requests.

---

## 🧪 Testing Configuration

### Test Database (H2)

Create `application-test.properties`:
```properties
spring.datasource.url=jdbc:h2:mem:testdb
spring.datasource.driverClassName=org.h2.Driver
spring.datasource.username=sa
spring.datasource.password=
spring.jpa.database-platform=org.hibernate.dialect.H2Dialect
spring.h2.console.enabled=true
```

Run tests with:
```bash
mvn test -Dspring.profiles.active=test
```

---

## 🌍 Environment-Specific Profiles

### Create Multiple Configurations

**application-dev.properties:**
```properties
spring.datasource.url=jdbc:mysql://localhost:3306/task_management_dev
spring.jpa.hibernate.ddl-auto=create-drop
spring.jpa.show-sql=true
logging.level.root=DEBUG
```

**application-production.properties:**
```properties
spring.datasource.url=jdbc:mysql://prod-db:3306/task_management
spring.jpa.hibernate.ddl-auto=validate
spring.jpa.show-sql=false
logging.level.root=WARN
```

### Run with Specific Profile

```bash
# Development
mvn spring-boot:run -Dspring-boot.run.arguments="--spring.profiles.active=dev"

# Production
mvn spring-boot:run -Dspring-boot.run.arguments="--spring.profiles.active=production"

# Using JAR
java -jar target/task-management-0.0.1-SNAPSHOT.jar --spring.profiles.active=production
```

---

## 🗄️ Connection Pool Configuration

### HikariCP Settings

Add to `application.properties`:
```properties
# Connection pool size
spring.datasource.hikari.maximum-pool-size=20
spring.datasource.hikari.minimum-idle=5
spring.datasource.hikari.connection-timeout=30000
spring.datasource.hikari.idle-timeout=600000
spring.datasource.hikari.max-lifetime=1800000
```

---

## 📱 Actuator Endpoints (Health Check)

### Enable Actuator

Add to pom.xml:
```xml
<dependency>
    <groupId>org.springframework.boot</groupId>
    <artifactId>spring-boot-starter-actuator</artifactId>
</dependency>
```

Configure in `application.properties`:
```properties
management.endpoints.web.exposure.include=health,info,metrics
management.endpoint.health.show-details=when-authorized
```

Access at:
- http://localhost:8080/actuator/health
- http://localhost:8080/actuator/metrics

---

## 🚀 Performance Tuning

### Database Connection Optimization

```properties
# Connection pool
spring.datasource.hikari.maximum-pool-size=30
spring.datasource.hikari.minimum-idle=10

# JPA batch settings
spring.jpa.properties.hibernate.jdbc.batch_size=20
spring.jpa.properties.hibernate.order_inserts=true
spring.jpa.properties.hibernate.order_updates=true

# Query cache
spring.jpa.properties.hibernate.cache.use_query_cache=true
spring.jpa.properties.hibernate.cache.use_second_level_cache=true
```

### Lazy Loading Configuration

```properties
# Open Session in View (caution: potential performance impact)
spring.jpa.open-in-view=true
```

---

## 🔄 Transaction Configuration

```properties
# Transaction timeout (in seconds)
spring.jpa.properties.hibernate.jdbc.time_zone=UTC

# Transaction isolation
spring.jpa.properties.hibernate.connection.isolation=2
```

---

## 📂 File Upload Configuration (Future Feature)

```properties
# File upload size
spring.servlet.multipart.max-file-size=10MB
spring.servlet.multipart.max-request-size=10MB
```

---

## ✉️ Email Configuration (Future Feature)

```properties
# Email configuration
spring.mail.host=smtp.gmail.com
spring.mail.port=587
spring.mail.username=your-email@gmail.com
spring.mail.password=your-app-password
spring.mail.properties.mail.smtp.auth=true
spring.mail.properties.mail.smtp.starttls.enable=true
spring.mail.properties.mail.smtp.starttls.required=true
```

---

## 🌐 Multi-language Support (i18n) - Future Feature

```properties
# Message source
spring.messages.basename=messages
spring.messages.encoding=UTF-8
spring.messages.fallback-to-system-locale=false
```

---

## 📋 Complete Sample Configuration Files

### Development (application-dev.properties)

```properties
spring.application.name=task-management
spring.datasource.url=jdbc:mysql://localhost:3306/task_management_dev
spring.datasource.username=root
spring.datasource.password=root
spring.datasource.driver-class-name=com.mysql.cj.jdbc.Driver
spring.jpa.database-platform=org.hibernate.dialect.MySQLDialect
spring.jpa.hibernate.ddl-auto=create-drop
spring.jpa.show-sql=true
spring.jpa.properties.hibernate.format_sql=true

server.port=8080

logging.level.root=INFO
logging.level.com.example.task_management=DEBUG

spring.datasource.hikari.maximum-pool-size=10
spring.datasource.hikari.minimum-idle=2
```

### Production (application-prod.properties)

```properties
spring.application.name=task-management
spring.datasource.url=jdbc:mysql://prod-db-host:3306/task_management
spring.datasource.username=${DB_USERNAME}
spring.datasource.password=${DB_PASSWORD}
spring.datasource.driver-class-name=com.mysql.cj.jdbc.Driver
spring.jpa.database-platform=org.hibernate.dialect.MySQLDialect
spring.jpa.hibernate.ddl-auto=validate
spring.jpa.show-sql=false

server.port=8080
server.ssl.enabled=true

logging.level.root=WARN
logging.file.name=/var/log/task-management/app.log

spring.datasource.hikari.maximum-pool-size=30
spring.datasource.hikari.minimum-idle=10
```

---

## 🔧 Running with Custom Configuration

### Via Command Line

```bash
# Specify config file location
java -jar target/task-management-0.0.1-SNAPSHOT.jar --spring.config.location=file:/path/to/application.properties

# Override individual properties
java -jar target/task-management-0.0.1-SNAPSHOT.jar --spring.datasource.url=jdbc:mysql://newhost:3306/db

# With Maven
mvn spring-boot:run -Dspring-boot.run.arguments="--server.port=9090"
```

### Via Environment Variables

```bash
export SPRING_DATASOURCE_URL=jdbc:mysql://localhost:3306/task_management
export SPRING_DATASOURCE_USERNAME=root
export SPRING_DATASOURCE_PASSWORD=root

mvn spring-boot:run
```

### Via application.yml (Alternative to properties)

Create `src/main/resources/application.yml`:

```yaml
spring:
  application:
    name: task-management
  datasource:
    url: jdbc:mysql://localhost:3306/task_management
    username: root
    password: root
    driver-class-name: com.mysql.cj.jdbc.Driver
  jpa:
    database-platform: org.hibernate.dialect.MySQLDialect
    hibernate:
      ddl-auto: update
    show-sql: true

server:
  port: 8080

logging:
  level:
    root: INFO
```

---

## 📚 Additional Resources

- [Spring Boot Application Properties Documentation](https://docs.spring.io/spring-boot/docs/4.0.5/reference/html/application-properties.html)
- [Spring Data JPA Reference](https://docs.spring.io/spring-data/jpa/docs/current/reference/html/)
- [Hibernate Configuration Guide](https://docs.jboss.org/hibernate/orm/current/userguide/html_single/Hibernate_User_Guide.html)
- [MySQL Connector/J Properties](https://dev.mysql.com/doc/connector-j/8.0/en/connector-j-reference-properties.html)

---

*Configuration Reference v1.0 - April 18, 2026*
