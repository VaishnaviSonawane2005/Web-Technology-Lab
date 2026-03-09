import React from "react";

function EventList({ events = [], onRegister, compact = false }) {

  return (

<section className="page-section">

<div className="section-head">
<h2 className="section-title">Event Details</h2>
<p className="section-subtitle">
Browse upcoming events with venue details, mentors, seats, and participation mode.
</p>
</div>

<div className={`events-grid ${compact ? "events-grid-compact" : ""}`}>

{events.map(event => (

<article className="event-card" key={event.id}>
<img src={event.image} alt={event.title} className="event-image"/>

<div className="event-body">

<h5 className="card-title">{event.title}</h5>
<span className="event-chip">{event.category}</span>

<p className="event-meta">

<strong>Club:</strong> {event.club} <br/>
<strong>Mentor:</strong> {event.mentor} <br/>
<strong>Date:</strong> {event.date} <br/>
<strong>Location:</strong> {event.location} <br/>
<strong>Mode:</strong> {event.mode} <br/>
<strong>Seats:</strong> {event.seats}

</p>

<button
className="app-btn"
type="button"
onClick={() => onRegister && onRegister(event.id)}
>
Register
</button>

</div>

</article>

))}

</div>

</section>

  );
}

export default EventList;
