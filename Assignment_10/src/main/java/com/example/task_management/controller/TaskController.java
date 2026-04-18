package com.example.task_management.controller;

import com.example.task_management.model.Task;
import com.example.task_management.repository.EmployeeRepository;
import com.example.task_management.repository.TaskRepository;
import jakarta.validation.Valid;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Controller;
import org.springframework.ui.Model;
import org.springframework.validation.BindingResult;
import org.springframework.web.bind.annotation.*;

@Controller
@RequestMapping("/tasks")
public class TaskController {

    @Autowired
    private TaskRepository taskRepository;

    @Autowired
    private EmployeeRepository employeeRepository;

    @GetMapping
    public String listTasks(Model model) {
        model.addAttribute("tasks", taskRepository.findAll());
        return "tasks/list";
    }

    @GetMapping("/new")
    public String showCreateForm(Model model) {
        model.addAttribute("task", new Task());
        model.addAttribute("employees", employeeRepository.findAll());
        return "tasks/form";
    }

    @PostMapping
    public String createTask(@Valid @ModelAttribute("task") Task task, BindingResult result, Model model) {
        if (result.hasErrors()) {
            model.addAttribute("employees", employeeRepository.findAll());
            return "tasks/form";
        }
        taskRepository.save(task);
        return "redirect:/tasks";
    }

    @PostMapping("/update-status/{id}")
    public String updateStatus(@PathVariable("id") Long id, @RequestParam("status") String status) {
        Task task = taskRepository.findById(id)
                .orElseThrow(() -> new IllegalArgumentException("Invalid task Id:" + id));
        task.setStatus(status);
        taskRepository.save(task);
        return "redirect:/tasks";
    }
    
    @PostMapping("/delete/{id}")
    public String deleteTask(@PathVariable("id") Long id) {
        Task task = taskRepository.findById(id)
                .orElseThrow(() -> new IllegalArgumentException("Invalid task Id:" + id));
        taskRepository.delete(task);
        return "redirect:/tasks";
    }
}
