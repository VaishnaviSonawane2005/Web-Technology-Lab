import React,{useState} from "react";

function AdminPanel({addEvent}){

const [event,setEvent]=useState({
title:"",
club:"",
date:"",
location:"",
seats:"",
category:"",
mentor:"",
mode:"Offline",
image:"",
video:""
});

const handleChange=(e)=>{
setEvent({...event,[e.target.name]:e.target.value});
};

const submit=(e)=>{
e.preventDefault();

addEvent({
...event,
id:Date.now(),
seats:Number(event.seats || 0),
image:event.image || "https://images.unsplash.com/photo-1523580846011-d3a5bc25702b?auto=format&fit=crop&w=900&q=80",
video:event.video || "https://www.youtube.com/embed/jNQXAC9IVRw"
});

alert("Event Added");
setEvent({
title:"",
club:"",
date:"",
location:"",
seats:"",
category:"",
mentor:"",
mode:"Offline",
image:"",
video:""
});
};

return(

<section className="page-section">

<div className="section-head">
<h2 className="section-title">Admin Panel</h2>
<p className="section-subtitle">
Add new events with extended details and media.
</p>
</div>

<div className="custom-card form-card">
<form className="registration-form" onSubmit={submit}>
<div className="form-grid">
<div className="field-block">
<label>Event Title</label>
<input className="app-input" name="title" value={event.title} onChange={handleChange} required/>
</div>

<div className="field-block">
<label>Club</label>
<input className="app-input" name="club" value={event.club} onChange={handleChange} required/>
</div>

<div className="field-block">
<label>Category</label>
<select className="app-input" name="category" value={event.category} onChange={handleChange} required>
<option value="">Select Category</option>
<option>Workshop</option>
<option>Competition</option>
<option>Seminar</option>
<option>Cultural</option>
</select>
</div>

<div className="field-block">
<label>Mentor / Speaker</label>
<input className="app-input" name="mentor" value={event.mentor} onChange={handleChange} required/>
</div>

<div className="field-block">
<label>Date</label>
<input type="date" className="app-input" name="date" value={event.date} onChange={handleChange} required/>
</div>

<div className="field-block">
<label>Location</label>
<input className="app-input" name="location" value={event.location} onChange={handleChange} required/>
</div>

<div className="field-block">
<label>Mode</label>
<select className="app-input" name="mode" value={event.mode} onChange={handleChange}>
<option>Offline</option>
<option>Online</option>
<option>Hybrid</option>
</select>
</div>

<div className="field-block">
<label>Seats</label>
<input type="number" className="app-input" name="seats" value={event.seats} onChange={handleChange} required/>
</div>

<div className="field-block field-block-wide">
<label>Event Image URL (Optional)</label>
<input className="app-input" name="image" value={event.image} onChange={handleChange}/>
</div>

<div className="field-block field-block-wide">
<label>YouTube Embed URL (Optional)</label>
<input className="app-input" name="video" value={event.video} onChange={handleChange}/>
</div>
</div>

<button className="app-btn">
Add Event
</button>

</form>

</div>

</section>

)

}

export default AdminPanel;
