import { Component } from '@angular/core';
import { RouterModule } from '@angular/router';
import { FormsModule } from '@angular/forms';
import { CommonModule } from '@angular/common';  // ADD THIS

@Component({
  selector: 'app-register',
  standalone: true,
  imports: [RouterModule, FormsModule, CommonModule],  // ADD HERE
  templateUrl: './register.component.html',
  styleUrl: './register.component.scss'
})
export class RegisterComponent {

  calculatePercentage(obtained: number, total: number): number {
    if (!total || total <= 0) return 0;
    return parseFloat(((obtained / total) * 100).toFixed(2));
  }

  onSubmit(form: any) {
    if (form.valid) {
      alert("Registration Successful!");
      form.reset();
    }
  }
}