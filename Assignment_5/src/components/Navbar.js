import React from "react";

const navItems = [
  { id: "home", label: "Home" },
  { id: "events", label: "Events" },
  { id: "clubs", label: "Club Activities" },
  { id: "register", label: "Register" },
  { id: "media", label: "Media" },
  { id: "admin", label: "Admin" }
];

function Navbar({ activePage, onPageChange }) {

  return (

<nav className="custom-navbar">

<div className="container">

<div className="brand-block">
  <span className="brand-title">Engineering College Events</span>
  <span className="brand-subtitle">Campus Connect</span>
</div>

<div className="nav-links">
{navItems.map(item => (
<button
key={item.id}
type="button"
className={`nav-link-btn ${activePage === item.id ? "active-link" : ""}`}
onClick={() => onPageChange(item.id)}
>
{item.label}
</button>
))}
</div>

</div>

</nav>

  );
}

export default Navbar;
