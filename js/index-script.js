lucide.createIcons();

document.addEventListener("DOMContentLoaded", () => {
  const searchInput = document.getElementById("menuSearchInput");
  const filterButtons = document.querySelectorAll(".filter-trigger");
  const sections = document.querySelectorAll(".menu-catalog-section");
  const zeroResultsMessage = document.getElementById("zeroResultsMessage");
  const categoryCardsWrapper = document.getElementById("categoryCardsWrapper");
  const categoryCards = document.querySelectorAll(".category-card-node");

  // ─── PROFESSIONAL SLIDESHOW ───────────────────────────────────────
  (function initSlideshow() {
    const slides = document.querySelectorAll(".hero-slide");
    const dots = document.querySelectorAll(".slideshow-dot");
    const fill = document.getElementById("slideshowProgressFill");
    const prevBtn = document.getElementById("slidePrev");
    const nextBtn = document.getElementById("slideNext");
    const heroShell = document.querySelector(".menu-hero-shell");

    const DURATION = 6500; // ms per slide (matches CSS transition 6.5s)
    let current = 0;
    let timer = null;
    let isPaused = false;

    if (!slides.length) return;

    function activateSlide(index) {
      // Deactivate old slide
      slides[current].classList.remove("is-active");
      slides[current].classList.add("is-leaving");
      dots[current].classList.remove("is-active");

      // Reset the leaving slide's Ken Burns after it's gone (1.6s fade)
      const leaving = slides[current];
      setTimeout(() => {
        leaving.classList.remove("is-leaving");
        // Reset the inner img so animation replays next time
        const img = leaving.querySelector(".hero-slide__img");
        img.style.animation = "none";
        // Force reflow then clear inline override
        void img.offsetWidth;
        img.style.animation = "";
      }, 1700);

      current = index;

      // Activate new slide
      slides[current].classList.add("is-active");
      dots[current].classList.add("is-active");

      // Restart progress bar
      fill.classList.remove("is-running");
      fill.style.width = "0%";
      // Tiny rAF delay so the browser registers the width reset
      requestAnimationFrame(() => {
        requestAnimationFrame(() => {
          fill.classList.add("is-running");
          fill.style.width = "100%";
        });
      });
    }

    function goNext() {
      const next = (current + 1) % slides.length;
      activateSlide(next);
    }

    function goPrev() {
      const prev = (current - 1 + slides.length) % slides.length;
      activateSlide(prev);
    }

    function startTimer() {
      clearInterval(timer);
      timer = setInterval(() => {
        if (!isPaused) goNext();
      }, DURATION);
    }

    // Dot clicks
    dots.forEach((dot) => {
      dot.addEventListener("click", () => {
        const idx = parseInt(dot.getAttribute("data-dot"), 10);
        if (idx === current) return;
        activateSlide(idx);
        startTimer(); // reset interval so dot-clicked slide gets full duration
      });
    });

    // Arrow clicks
    if (prevBtn)
      prevBtn.addEventListener("click", () => {
        goPrev();
        startTimer();
      });
    if (nextBtn)
      nextBtn.addEventListener("click", () => {
        goNext();
        startTimer();
      });

    // Pause on hover
    if (heroShell) {
      heroShell.addEventListener("mouseenter", () => {
        isPaused = true;
      });
      heroShell.addEventListener("mouseleave", () => {
        isPaused = false;
      });
    }

    // Keyboard navigation (when hero is focused)
    document.addEventListener("keydown", (e) => {
      if (e.key === "ArrowLeft") {
        goPrev();
        startTimer();
      }
      if (e.key === "ArrowRight") {
        goNext();
        startTimer();
      }
    });

    // Swipe support for mobile
    let touchStartX = 0;
    heroShell.addEventListener(
      "touchstart",
      (e) => {
        touchStartX = e.changedTouches[0].clientX;
      },
      { passive: true },
    );
    heroShell.addEventListener(
      "touchend",
      (e) => {
        const dx = e.changedTouches[0].clientX - touchStartX;
        if (Math.abs(dx) < 40) return;
        if (dx < 0) goNext();
        else goPrev();
        startTimer();
      },
      { passive: true },
    );

    // Kick off
    activateSlide(0);
    startTimer();
  })();
  categoryCards.forEach((card) => {
    card.addEventListener("click", () => {
      const targetCat = card.getAttribute("data-trigger-cat");
      const targetBtn = document.querySelector(
        `.filter-trigger[data-target-cat="${targetCat}"]`,
      );
      if (targetBtn) targetBtn.click();
    });
  });

  function filterCatalog() {
    const searchVal = searchInput ? searchInput.value.toLowerCase().trim() : "";
    const activeBtn = document.querySelector(".filter-trigger.active");
    const activeCategory = activeBtn
      ? activeBtn.getAttribute("data-target-cat")
      : "all";
    let overallVisibleItems = 0;

    // Show dynamic category cards if "all" is active and user isn't searching
    if (activeCategory === "all" && searchVal === "") {
      if (categoryCardsWrapper) categoryCardsWrapper.classList.remove("hidden");
      sections.forEach((section) => (section.style.display = "none"));
      if (zeroResultsMessage) zeroResultsMessage.classList.add("hidden");
      return;
    }

    // Otherwise, hide the category grid and process filtering
    if (categoryCardsWrapper) categoryCardsWrapper.classList.add("hidden");

    sections.forEach((section) => {
      const sectionCategoryName = section.getAttribute("data-cat-group");
      const cards = section.querySelectorAll(".catalog-item-node");
      let visibleCardsInSection = 0;

      const isCategoryMatch =
        activeCategory === "all" || sectionCategoryName === activeCategory;

      cards.forEach((card) => {
        const title = card.getAttribute("data-item-title") || "";
        const desc = card.getAttribute("data-item-details") || "";
        const isSearchMatch =
          title.includes(searchVal) || desc.includes(searchVal);

        if (isCategoryMatch && isSearchMatch) {
          card.style.display = "flex";
          visibleCardsInSection++;
          overallVisibleItems++;
        } else {
          card.style.display = "none";
        }
      });

      if (visibleCardsInSection > 0) {
        section.style.display = "block";
      } else {
        section.style.display = "none";
      }
    });

    if (zeroResultsMessage) {
      if (overallVisibleItems === 0) {
        zeroResultsMessage.classList.remove("hidden");
      } else {
        zeroResultsMessage.classList.add("hidden");
      }
    }
  }

  if (searchInput) {
    searchInput.addEventListener("input", filterCatalog);
  }

  filterButtons.forEach((btn) => {
    btn.addEventListener("click", () => {
      filterButtons.forEach((b) => {
        b.classList.remove("active", "border-hadero-gold", "text-zinc-900");
        b.classList.add("border-transparent", "text-zinc-400");
      });
      btn.classList.add("active", "border-hadero-gold", "text-zinc-900");
      btn.classList.remove("border-transparent", "text-zinc-400");
      filterCatalog();
    });
  });

  // Initialize the correct view on load
  filterCatalog();
});
