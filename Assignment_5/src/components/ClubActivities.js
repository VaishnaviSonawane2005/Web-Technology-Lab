import React from "react";

const clubs = [
  {
    id: 1,
    name: "Coding Club",
    focus: "Hackathons, DSA prep, and open-source contribution drives.",
    frequency: "Every Friday",
    lead: "Prof. Vivek Joshi"
  },
  {
    id: 2,
    name: "Robotics Circle",
    focus: "Embedded systems, automation projects, and bot competitions.",
    frequency: "Every Wednesday",
    lead: "Dr. Sneha Iyer"
  },
  {
    id: 3,
    name: "Design & Media Club",
    focus: "UI/UX sprints, brand design, and visual storytelling workshops.",
    frequency: "Alternate Saturdays",
    lead: "Ms. Rhea Shah"
  }
];

function ClubActivities() {
  return (
    <section className="page-section">
      <div className="section-head">
        <h2 className="section-title">Club Activities</h2>
        <p className="section-subtitle">
          Regular sessions and hands-on communities to build practical skills.
        </p>
      </div>

      <div className="club-grid">
        {clubs.map((club) => (
          <article key={club.id} className="custom-card club-card">
            <h3>{club.name}</h3>
            <p>{club.focus}</p>
            <div className="club-meta">
              <span>{club.frequency}</span>
              <span>{club.lead}</span>
            </div>
          </article>
        ))}
      </div>
    </section>
  );
}

export default ClubActivities;
