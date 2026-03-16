import React from "react";

function MediaGallery({ events = [] }) {
  const featuredEvents = events.slice(0, 2);

  return (
    <section className="page-section">
      <div className="section-head">
        <h2 className="section-title">Media Gallery</h2>
        <p className="section-subtitle">
          Event highlights, behind-the-scenes visuals, and teaser videos.
        </p>
      </div>

      <div className="media-grid">
        {events.map((event) => (
          <article key={event.id} className="custom-card media-card">
            <img src={event.image} alt={event.title} className="media-image" />
            <div className="media-body">
              <h3>{event.title}</h3>
              <p>{event.club}</p>
            </div>
          </article>
        ))}
      </div>

      <div className="video-grid">
        {featuredEvents.map((event) => (
          <article key={event.id} className="custom-card video-card">
            <iframe
              title={`${event.title} teaser`}
              src={event.video}
              allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
              allowFullScreen
            />
            <div className="video-caption">
              <h4>{event.title}</h4>
              <p>Preview session and event highlights</p>
            </div>
          </article>
        ))}
      </div>
    </section>
  );
}

export default MediaGallery;
