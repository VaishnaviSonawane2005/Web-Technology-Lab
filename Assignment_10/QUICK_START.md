# 🚀 QUICK START GUIDE

## ⚡ 30-Second Startup

```bash
cd c:\Users\vaish\OneDrive\Desktop\WTL\Assignment_10
run.bat
```

Then open: **http://localhost:8080** in your browser

---

## 📋 Essential Commands

| Task | Command |
|------|---------|
| **Build** | `mvn clean install` |
| **Run** | `mvn spring-boot:run` |
| **Run JAR** | `java -jar target/task-management-0.0.1-SNAPSHOT.jar` |
| **Test** | `mvn test` |
| **Clean** | `mvn clean` |

---

## 🌐 Access Points

| Page | URL |
|------|-----|
| Dashboard | http://localhost:8080 |
| Employees | http://localhost:8080/employees |
| Tasks | http://localhost:8080/tasks |
| Create Employee | http://localhost:8080/employees/new |
| Create Task | http://localhost:8080/tasks/new |

---

## 📁 Important Files

| File | Purpose |
|------|---------|
| `SETUP_GUIDE.md` | Complete setup documentation |
| `PROJECT_SETUP_COMPLETE.md` | Setup verification report |
| `CONFIGURATION_REFERENCE.md` | All configuration options |
| `run.bat` | Windows startup script |
| `run.sh` | Linux/Mac startup script |
| `pom.xml` | Maven build configuration |

---

## 🔧 Database Credentials

| Property | Value |
|----------|-------|
| Host | localhost |
| Port | 3306 |
| Database | task_management |
| Username | root |
| Password | root |

---

## ✅ Status

- ✅ Build: SUCCESS
- ✅ Tests: PASSING (1/1)
- ✅ Database: CONNECTED
- ✅ Ready to Run: YES

---

## 🐛 Quick Troubleshooting

| Problem | Solution |
|---------|----------|
| MySQL won't connect | Start MySQL: `mysql -u root -proot` |
| Port 8080 in use | Change: `server.port=8081` in application.properties |
| Build fails | Run: `mvn clean install -U` |
| No database | Restart app - auto-creates tables |

---

## 📚 Need More Info?

Read the detailed guides:
- `SETUP_GUIDE.md` - Full setup instructions
- `CONFIGURATION_REFERENCE.md` - All configuration options
- `PROJECT_SETUP_COMPLETE.md` - Complete verification report

---

**Everything is ready! Start with: `run.bat` or `mvn spring-boot:run`** 🚀
