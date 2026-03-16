import React, { useState } from "react";
import Navbar from "./components/Navbar";
import EventList from "./components/EventList";
import RegisterSection from "./components/RegisterSection";
import AdminPanel from "./components/AdminPanel";
import ClubActivities from "./components/ClubActivities";
import MediaGallery from "./components/MediaGallery";
import "./App.css";

function App() {
  const [activePage, setActivePage] = useState("home");
  const [selectedEventId, setSelectedEventId] = useState("");

  const [events, setEvents] = useState([
    {
      id: 1,
      title: "AI & Machine Learning Workshop",
      club: "Technical Club",
      date: "2026-04-10",
      location: "Seminar Hall",
      seats: 50,
      category: "Workshop",
      mentor: "Dr. Ananya Patil",
      mode: "Offline",
      image:
        "https://images.unsplash.com/photo-1519389950473-47ba0277781c?auto=format&fit=crop&w=900&q=80",
      video: "https://www.youtube.com/embed/aircAruvnKk"
    },
    {
      id: 2,
      title: "National Level Hackathon",
      club: "Coding Club",
      date: "2026-04-15",
      location: "Innovation Lab",
      seats: 100,
      category: "Competition",
      mentor: "Prof. Rohan Kulkarni",
      mode: "Hybrid",
      image:
        "https://images.unsplash.com/photo-1517048676732-d65bc937f952?auto=format&fit=crop&w=900&q=80",
      video: "https://www.youtube.com/embed/3qBXWUpoPHo"
    },
    {
      id: 3,
      title: "Design Thinking Sprint",
      club: "Innovation Club",
      date: "2026-04-24",
      location: "Creative Studio",
      seats: 70,
      category: "Ideation",
      mentor: "Ms. Nidhi Sharma",
      mode: "Offline",
      image:
        "https://images.unsplash.com/photo-1552664730-d307ca884978?auto=format&fit=crop&w=900&q=80",
      video: "https://www.youtube.com/embed/_r0VX-aU_T8"
    }
  ]);

  const addEvent = (event) => {
    setEvents([...events, event]);
  };

  const stats = [
    { label: "Total Events", value: events.length },
    { label: "Active Clubs", value: "7+" },
    { label: "Student Participants", value: "450+" }
  ];

  const handleRegisterClick = (eventId) => {
    setSelectedEventId(String(eventId));
    setActivePage("register");
  };

  return (
    <div className="app-shell">
      <Navbar activePage={activePage} onPageChange={setActivePage} />

      <main className="container app-content">
        {activePage === "home" && (
          <>
            <section className="hero">
              <div className="hero-badge">Engineering College Community</div>
              <h1>College Event Management Portal</h1>
              <p>
                Discover technical workshops, club activities, competitions, and
                networking opportunities in one place.
              </p>
              <div className="hero-actions">
                <button
                  className="app-btn"
                  type="button"
                  onClick={() => setActivePage("events")}
                >
                  Explore Events
                </button>
                <button
                  className="app-btn app-btn-outline"
                  type="button"
                  onClick={() => setActivePage("register")}
                >
                  Quick Registration
                </button>
              </div>
            </section>

            <section className="stats-grid">
              {stats.map((item) => (
                <article key={item.label} className="stat-card">
                  <h3>{item.value}</h3>
                  <p>{item.label}</p>
                </article>
              ))}
            </section>

            <EventList
              events={events}
              onRegister={handleRegisterClick}
              compact
            />
          </>
        )}

        {activePage === "events" && (
          <EventList events={events} onRegister={handleRegisterClick} />
        )}

        {activePage === "clubs" && <ClubActivities />}

        {activePage === "register" && (
          <RegisterSection
            events={events}
            selectedEventId={selectedEventId}
            onEventSelected={setSelectedEventId}
          />
        )}

        {activePage === "media" && <MediaGallery events={events} />}

        {activePage === "admin" && <AdminPanel addEvent={addEvent} />}
      </main>

      <footer className="app-footer">
        <p>Engineering College Event Management System</p>
      </footer>
    </div>
  );
}

export default App;
