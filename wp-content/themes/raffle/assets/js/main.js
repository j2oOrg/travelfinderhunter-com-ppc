document.addEventListener("DOMContentLoaded", () => {
  // Contact form validation
  const form = document.getElementById("hubForm");
  const status = document.getElementById("hubStatus");

  if (form && status) {
    form.addEventListener("submit", (e) => {
      e.preventDefault();
      status.textContent = "";
      status.className = "form-status-h1m6";

      const name = document.getElementById("hubName").value.trim();
      const email = document.getElementById("hubEmail").value.trim();
      const message = document.getElementById("hubMessage").value.trim();
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
        status.className = "form-status-h1m6";
      }, 4000);
    });
  }

  // Scroll to top
  const scrollBtn = document.getElementById("hubScrollTop");
  if (scrollBtn) {
    window.addEventListener("scroll", () => {
      if (window.scrollY > 300) scrollBtn.classList.add("show");
      else scrollBtn.classList.remove("show");
    });
    scrollBtn.addEventListener("click", () => window.scrollTo({ top: 0, behavior: "smooth" }));
  }

  // Signup modal (front page)
  const signupModal = document.getElementById("signupModal");
  const openSignup = document.querySelectorAll("[data-signup-btn]");
  const closeSignup = signupModal ? signupModal.querySelector("[data-modal-close]") : null;
  const toggleModal = (show) => {
    if (!signupModal) return;
    signupModal.classList.toggle("active", show);
    signupModal.setAttribute("aria-hidden", show ? "false" : "true");
  };
  if (signupModal) {
    openSignup.forEach((btn) =>
      btn.addEventListener("click", (e) => {
        e.preventDefault();
        toggleModal(true);
      })
    );
    closeSignup?.addEventListener("click", () => toggleModal(false));
    signupModal.addEventListener("click", (e) => {
      if (e.target === signupModal) toggleModal(false);
    });
    document.addEventListener("keydown", (e) => {
      if (e.key === "Escape") toggleModal(false);
    });
  }

  // Signup form validation (modal)
  const signupForm = document.getElementById("signupForm");
  const signupStatus = document.getElementById("signupStatus");
  if (signupForm && signupStatus) {
    signupForm.addEventListener("submit", (e) => {
      e.preventDefault();
      signupStatus.textContent = "";
      signupStatus.className = "modal-status";

      const name = document.getElementById("signupName").value.trim();
      const email = document.getElementById("signupEmail").value.trim();
      const phone = document.getElementById("signupPhone").value.trim();
      const message = document.getElementById("signupMessage").value.trim();
      const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

      if (!name || !email || !phone || !message) {
        signupStatus.textContent = "Please complete all fields before submitting.";
        signupStatus.classList.add("error");
        return;
      }
      if (!emailPattern.test(email)) {
        signupStatus.textContent = "Please enter a valid email address.";
        signupStatus.classList.add("error");
        return;
      }

      signupForm.reset();
      signupStatus.textContent = "Message sent! We’ll reply with purchase details.";
      signupStatus.classList.add("success");
      setTimeout(() => {
        signupStatus.textContent = "";
        signupStatus.className = "modal-status";
        toggleModal(false);
      }, 3500);
    });
  }

  // Countdown timer (front page)
  const daysEl = document.getElementById("countdownDays");
  const hoursEl = document.getElementById("countdownHours");
  const minutesEl = document.getElementById("countdownMinutes");
  const secondsEl = document.getElementById("countdownSeconds");
  if (daysEl && hoursEl && minutesEl && secondsEl) {
    const target = new Date("February 25, 2026 00:00:00").getTime();
    const updateCountdown = () => {
      const now = Date.now();
      const diff = Math.max(target - now, 0);
      const days = Math.floor(diff / (1000 * 60 * 60 * 24));
      const hours = Math.floor((diff / (1000 * 60 * 60)) % 24);
      const minutes = Math.floor((diff / (1000 * 60)) % 60);
      const seconds = Math.floor((diff / 1000) % 60);
      daysEl.textContent = String(days).padStart(2, "0");
      hoursEl.textContent = String(hours).padStart(2, "0");
      minutesEl.textContent = String(minutes).padStart(2, "0");
      secondsEl.textContent = String(seconds).padStart(2, "0");
    };
    updateCountdown();
    setInterval(updateCountdown, 1000);
  }

  // Footer year
  document.querySelectorAll("[data-year]").forEach((el) => {
    el.textContent = new Date().getFullYear();
  });
});

