import React from "react";

function EventCard({ event }) {

  return (

    <div className="event-card">

      <div className="event-header">
        <h3>{event.title}</h3>
        <span className="club-tag">{event.club}</span>
      </div>

      <div className="event-details">

        <p>
          <span className="label">Date</span>
          {event.date}
        </p>

        <p>
          <span className="label">Location</span>
          {event.location}
        </p>

        <p>
          <span className="label">Seats</span>
          {event.seats} Available
        </p>

      </div>

      <button className="register-btn">
        Register
      </button>

    </div>

  );
}

export default EventCard;