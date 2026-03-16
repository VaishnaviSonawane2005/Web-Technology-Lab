import React, { useEffect, useState } from "react";


function RegisterSection({ events = [], selectedEventId = "", onEventSelected }) {

const [form, setForm] = useState({
name:"",
email:"",
phone:"",
studentId:"",
department:"",
year:"Second Year",
semester:"Semester 4",
eventId:"",
interestArea:"",
experience:"",
portfolio:"",
comments:"",
consent:false
});

const handleChange=(e)=>{
const { name, value, type, checked } = e.target;
setForm({...form,[name]:type === "checkbox" ? checked : value});
};

const submitForm=(e)=>{
e.preventDefault();
alert("Registration Successful!");
setForm({
name:"",
email:"",
phone:"",
studentId:"",
department:"",
year:"Second Year",
semester:"Semester 4",
eventId:selectedEventId || "",
interestArea:"",
experience:"",
portfolio:"",
comments:"",
consent:false
});
};

useEffect(() => {
setForm((prev) => ({ ...prev, eventId: selectedEventId || prev.eventId }));
}, [selectedEventId]);

return(

<section className="page-section">

<div className="section-head">
<h2 className="section-title">Registration Form</h2>
<p className="section-subtitle">
Complete your details to reserve your seat in the selected event.
</p>
</div>

<div className="custom-card form-card">
<form className="registration-form" onSubmit={submitForm}>

<div className="form-grid">

<div className="field-block">
<label>Name</label>
<input className="app-input" name="name" value={form.name} onChange={handleChange} required/>
</div>

<div className="field-block">
<label>Email</label>
<input type="email" className="app-input" name="email" value={form.email} onChange={handleChange} required/>
</div>

<div className="field-block">
<label>Phone</label>
<input className="app-input" name="phone" value={form.phone} onChange={handleChange} required/>
</div>

<div className="field-block">
<label>Student ID</label>
<input className="app-input" name="studentId" value={form.studentId} onChange={handleChange} required/>
</div>

<div className="field-block">
<label>Department</label>
<select className="app-input" name="department" value={form.department} onChange={handleChange} required>
<option value="">Select Department</option>
<option>Computer Engineering</option>
<option>Information Technology</option>
<option>Electronics</option>
<option>Mechanical</option>
<option>Civil Engineering</option>
</select>
</div>

<div className="field-block">
<label>Year</label>
<select className="app-input" name="year" value={form.year} onChange={handleChange}>
<option>First Year</option>
<option>Second Year</option>
<option>Third Year</option>
<option>Final Year</option>
</select>
</div>

<div className="field-block">
<label>Semester</label>
<select className="app-input" name="semester" value={form.semester} onChange={handleChange}>
<option>Semester 1</option>
<option>Semester 2</option>
<option>Semester 3</option>
<option>Semester 4</option>
<option>Semester 5</option>
<option>Semester 6</option>
<option>Semester 7</option>
<option>Semester 8</option>
</select>
</div>

<div className="field-block">
<label>Select Event</label>
<select
className="app-input"
name="eventId"
value={form.eventId}
onChange={(e) => {
handleChange(e);
if (onEventSelected) {
onEventSelected(e.target.value);
}
}}
required
>
<option value="">Select Event</option>

{events.map(e => (
<option key={e.id} value={e.id}>{e.title}</option>
))}

</select>
</div>

<div className="field-block">
<label>Interest Area</label>
<input
className="app-input"
name="interestArea"
placeholder="AI, Web, Cybersecurity, Design..."
value={form.interestArea}
onChange={handleChange}
required
/>
</div>

<div className="field-block">
<label>Prior Experience</label>
<select className="app-input" name="experience" value={form.experience} onChange={handleChange} required>
<option value="">Select Experience</option>
<option>Beginner</option>
<option>Intermediate</option>
<option>Advanced</option>
</select>
</div>

<div className="field-block field-block-wide">
<label>Portfolio / LinkedIn (Optional)</label>
<input
className="app-input"
name="portfolio"
placeholder="https://..."
value={form.portfolio}
onChange={handleChange}
/>
</div>

<div className="field-block field-block-wide">
<label>Why do you want to join?</label>
<textarea
className="app-input app-textarea"
name="comments"
rows="4"
value={form.comments}
onChange={handleChange}
/>
</div>

</div>

<label className="consent-row">
<input
type="checkbox"
name="consent"
checked={form.consent}
onChange={handleChange}
required
/>
<span>I confirm that the information provided is accurate.</span>
</label>

<button className="app-btn">
Register
</button>

</form>
</div>
</section>

)

}

export default RegisterSection;
