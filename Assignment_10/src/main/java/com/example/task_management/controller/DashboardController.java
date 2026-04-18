package com.example.task_management.controller;

import com.example.task_management.repository.EmployeeRepository;
import com.example.task_management.repository.TaskRepository;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Controller;
import org.springframework.ui.Model;
import org.springframework.web.bind.annotation.GetMapping;

@Controller
public class DashboardController {

    @Autowired
    private EmployeeRepository employeeRepository;

    @Autowired
    private TaskRepository taskRepository;

    @GetMapping("/")
    public String index(Model model) {
        model.addAttribute("totalEmployees", employeeRepository.count());
        model.addAttribute("totalTasks", taskRepository.count());
        model.addAttribute("pendingTasks", taskRepository.countByStatus("PENDING"));
        model.addAttribute("completedTasks", taskRepository.countByStatus("COMPLETED"));
        model.addAttribute("recentTasks", taskRepository.findTop5ByOrderByIdDesc());
        
        // Data for Kanban View
        model.addAttribute("allTasks", taskRepository.findAll());
        
        // Data for the Assign Task Modal Form
        model.addAttribute("employees", employeeRepository.findAll());
        model.addAttribute("task", new com.example.task_management.model.Task());
        
        return "index";
    }
}
