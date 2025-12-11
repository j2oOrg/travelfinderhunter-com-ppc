document.addEventListener("DOMContentLoaded", () => {
  initContactForm();
  initScrollTop();
  setFooterYear();
  initCountdown();
  initNavToggle();
  initModals();
});

function initContactForm() {
  const form = document.getElementById("hubForm");
  const status = document.getElementById("hubStatus");
  if (!form || !status) return;

  form.addEventListener("submit", (e) => {
    e.preventDefault();
    status.textContent = "";
    status.className = "form-status";

    const name = document.getElementById("hubName")?.value.trim();
    const email = document.getElementById("hubEmail")?.value.trim();
    const message = document.getElementById("hubMessage")?.value.trim();
    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    if (!name || !email || !message) {
      status.textContent = "Please fill in all fields before submitting.";
      status.classList.add("error");
      return;
    }
    if (!emailPattern.test(email)) {
      status.textContent = "Please enter a valid email address.";
      status.classList.add("error");
      return;
    }

    form.reset();
    status.textContent = "Message sent successfully! We’ll reply within 48 hours.";
    status.classList.add("success");

    setTimeout(() => {
      status.textContent = "";
      status.className = "form-status";
    }, 4000);
  });
}

function initScrollTop() {
  const scrollBtn = document.getElementById("hubScrollTop");
  if (!scrollBtn) return;

  window.addEventListener("scroll", () => {
    if (window.scrollY > 300) scrollBtn.classList.add("show");
    else scrollBtn.classList.remove("show");
  });
  scrollBtn.addEventListener("click", () => window.scrollTo({ top: 0, behavior: "smooth" }));
}

function setFooterYear() {
  document.querySelectorAll("[data-year]").forEach((el) => {
    el.textContent = new Date().getFullYear();
  });
}

function initCountdown() {
  const daysEl = document.getElementById("countdownDays");
  const hoursEl = document.getElementById("countdownHours");
  const minutesEl = document.getElementById("countdownMinutes");
  const secondsEl = document.getElementById("countdownSeconds");
  if (!daysEl || !hoursEl || !minutesEl || !secondsEl) return;

  const targetDate = new Date("December 26, 2025 12:00:00").getTime();

  const update = () => {
    const now = Date.now();
    let diff = targetDate - now;
    if (diff <= 0) diff = 0;

    const days = Math.floor(diff / (1000 * 60 * 60 * 24));
    const hours = Math.floor((diff / (1000 * 60 * 60)) % 24);
    const minutes = Math.floor((diff / (1000 * 60)) % 60);
    const seconds = Math.floor((diff / 1000) % 60);

    daysEl.textContent = String(days).padStart(2, "0");
    hoursEl.textContent = String(hours).padStart(2, "0");
    minutesEl.textContent = String(minutes).padStart(2, "0");
    secondsEl.textContent = String(seconds).padStart(2, "0");
  };

  update();
  setInterval(update, 1000);
}

function initNavToggle() {
  const toggle = document.querySelector("[data-menu-toggle]");
  const menu = document.getElementById("navMenu");
  if (!toggle || !menu) return;

  toggle.addEventListener("click", () => {
    menu.classList.toggle("open");
  });
}

function initModals() {
  const signupModal = document.getElementById("signupModal");
  const body = document.body;

  const openModal = (modal) => {
    if (!modal) return;
    modal.classList.add("active");
    body.style.overflow = "hidden";
  };

  const closeModal = (modal) => {
    if (!modal) return;
    modal.classList.remove("active");
    if (![signupModal].some((item) => item && item.classList.contains("active"))) {
      body.style.overflow = "";
    }
  };

  document.querySelectorAll("[data-signup-btn]").forEach((btn) =>
    btn.addEventListener("click", (e) => {
      e.preventDefault();
      openModal(signupModal);
    })
  );

  document.querySelectorAll("[data-modal-close]").forEach((btn) =>
    btn.addEventListener("click", (e) => {
      e.preventDefault();
      closeModal(btn.closest(".zlottour-modal"));
    })
  );

  [signupModal].forEach((modal) => {
    modal?.addEventListener("click", (e) => {
      if (e.target === modal) closeModal(modal);
    });
  });

  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") closeModal(signupModal);
  });

  const signupForm = document.getElementById("signupForm");
  const signupStatus = document.getElementById("signupStatus");
  if (signupForm && signupStatus) {
    signupForm.addEventListener("submit", (e) => {
      e.preventDefault();
      signupStatus.textContent = "Message sent. We'll reach out about your ticket inquiry soon.";
      signupStatus.className = "modal-status success";
      signupForm.reset();
    });
  }
}
