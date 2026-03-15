document.addEventListener('DOMContentLoaded', () => {
  // ===== Particles =====
  const particlesEl = document.getElementById('particles');
  if (particlesEl) {
    for (let i = 0; i < 30; i++) {
      const p = document.createElement('div');
      p.className = 'particle';
      p.style.left = Math.random() * 100 + '%';
      p.style.animationDelay = Math.random() * 8 + 's';
      p.style.animationDuration = (6 + Math.random() * 6) + 's';
      const size = 1 + Math.random() * 3;
      p.style.width = size + 'px';
      p.style.height = size + 'px';
      particlesEl.appendChild(p);
    }
  }

  // ===== Nav scroll =====
  const nav = document.getElementById('nav');
  if (nav) {
    window.addEventListener('scroll', () => {
      nav.classList.toggle('scrolled', window.scrollY > 60);
    });
  }

  // ===== Mobile menu =====
  const mobileToggle = document.getElementById('mobileToggle');
  const mobileMenu = document.getElementById('mobileMenu');

  function closeMobile() {
    if (mobileToggle) mobileToggle.classList.remove('active');
    if (mobileMenu) mobileMenu.classList.remove('active');
  }

  if (mobileToggle && mobileMenu) {
    mobileToggle.addEventListener('click', () => {
      mobileToggle.classList.toggle('active');
      mobileMenu.classList.toggle('active');
    });

    // Close mobile menu when clicking a link
    mobileMenu.querySelectorAll('a').forEach(link => {
      link.addEventListener('click', closeMobile);
    });
  }

  // ===== Language Switcher =====
  const langSwitcherBtn = document.getElementById('langSwitcherBtn');
  const langSwitcherDropdown = document.getElementById('langSwitcherDropdown');

  if (langSwitcherBtn && langSwitcherDropdown) {
    langSwitcherBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      langSwitcherDropdown.classList.toggle('active');
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', (e) => {
      if (!langSwitcherBtn.contains(e.target) && !langSwitcherDropdown.contains(e.target)) {
        langSwitcherDropdown.classList.remove('active');
      }
    });
  }

  // ===== Scroll animations =====
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(e => {
      if (e.isIntersecting) {
        e.target.classList.add('visible');
        // Counter animation
        const counter = e.target.querySelector('[data-target]');
        if (counter) {
          const target = +counter.dataset.target;
          let current = 0;
          const step = target / 40;
          const timer = setInterval(() => {
            current += step;
            if (current >= target) { current = target; clearInterval(timer); }
            counter.textContent = Math.floor(current) + (target === 99 ? '%' : '+');
          }, 40);
        }
      }
    });
  }, { threshold: 0.15 });

  document.querySelectorAll('.animate-on-scroll').forEach(el => observer.observe(el));

  // ===== Smooth scroll for anchors =====
  document.querySelectorAll('a[href^="#"]').forEach(a => {
    a.addEventListener('click', e => {
      const href = a.getAttribute('href');
      if (href === '#') return;
      const target = document.querySelector(href);
      if (target) {
        e.preventDefault();
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        closeMobile();
      }
    });
  });

  // ===== Newsletter form AJAX submission =====
  const newsletterForm = document.getElementById('newsletterForm');
  if (newsletterForm) {
    newsletterForm.addEventListener('submit', function(e) {
      e.preventDefault();

      const messageEl = document.getElementById('newsletterMessage');
      const emailInput = this.querySelector('input[name="email"]');
      const submitBtn = this.querySelector('button[type="submit"]');
      const email = emailInput ? emailInput.value.trim() : '';

      if (!email) return;

      // Disable button during submission
      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.textContent = '送出中...';
      }

      const csrfToken = document.querySelector('meta[name="csrf-token"]');
      const token = csrfToken ? csrfToken.getAttribute('content') : '';

      fetch(this.action, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': token,
          'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify({ email: email }),
      })
      .then(response => {
        if (!response.ok) {
          return response.json().then(data => {
            throw new Error(data.message || '訂閱失敗，請稍後再試。');
          });
        }
        return response.json();
      })
      .then(data => {
        if (messageEl) {
          messageEl.textContent = data.message || '訂閱成功！感謝您的關注。';
          messageEl.className = 'newsletter-message success';
          messageEl.style.display = 'block';
        }
        if (emailInput) emailInput.value = '';
      })
      .catch(error => {
        if (messageEl) {
          messageEl.textContent = error.message || '訂閱失敗，請稍後再試。';
          messageEl.className = 'newsletter-message error';
          messageEl.style.display = 'block';
        }
      })
      .finally(() => {
        if (submitBtn) {
          submitBtn.disabled = false;
          submitBtn.textContent = '訂閱';
        }
        // Auto-hide message after 5s
        if (messageEl) {
          setTimeout(() => {
            messageEl.style.display = 'none';
          }, 5000);
        }
      });
    });
  }

  // ===== FAQ Accordion =====
  const faqAccordion = document.getElementById('faqAccordion');
  if (faqAccordion) {
    const faqQuestions = faqAccordion.querySelectorAll('.faq-question');
    faqQuestions.forEach(question => {
      question.addEventListener('click', () => {
        const parentItem = question.closest('.faq-item');
        const isActive = parentItem.classList.contains('active');

        // Close all items
        faqAccordion.querySelectorAll('.faq-item').forEach(item => {
          item.classList.remove('active');
        });

        // Open clicked if it wasn't already open
        if (!isActive) {
          parentItem.classList.add('active');
        }
      });
    });
  }

  // ===== Blog Search — Enter key =====
  const blogSearchInput = document.querySelector('.blog-search-bar input');
  if (blogSearchInput) {
    blogSearchInput.addEventListener('keypress', (e) => {
      if (e.key === 'Enter') {
        e.target.closest('form').submit();
      }
    });
  }

  // ===== Portfolio Category Filter =====
  const portfolioFilterBtns = document.querySelectorAll('.portfolio-filters .category-pill');
  if (portfolioFilterBtns.length > 0) {
    portfolioFilterBtns.forEach(btn => {
      btn.addEventListener('click', () => {
        const filter = btn.dataset.filter;

        // Update active state
        portfolioFilterBtns.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');

        // Filter cards
        const cards = document.querySelectorAll('.portfolio-listing-card');
        cards.forEach(card => {
          if (filter === 'all' || card.dataset.category === filter) {
            card.style.display = '';
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            requestAnimationFrame(() => {
              card.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
              card.style.opacity = '1';
              card.style.transform = 'translateY(0)';
            });
          } else {
            card.style.display = 'none';
          }
        });
      });
    });
  }

  // ===== Floating Action Button (FAB) =====
  const fabContainer = document.getElementById('fabContainer');
  const fabToggle = document.getElementById('fabToggle');
  const fabBackdrop = document.getElementById('fabBackdrop');

  if (fabToggle && fabContainer) {
    fabToggle.addEventListener('click', () => {
      fabContainer.classList.toggle('active');
    });

    // Close when clicking backdrop
    if (fabBackdrop) {
      fabBackdrop.addEventListener('click', () => {
        fabContainer.classList.remove('active');
      });
    }

    // Close on Escape key
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && fabContainer.classList.contains('active')) {
        fabContainer.classList.remove('active');
      }
    });

    // Close when clicking a fab option (after a brief delay for UX)
    fabContainer.querySelectorAll('.fab-option').forEach(opt => {
      opt.addEventListener('click', () => {
        setTimeout(() => {
          fabContainer.classList.remove('active');
        }, 150);
      });
    });
  }

  // ===== Quote Calculator =====
  const quoteStepsContainer = document.getElementById('quoteStepsContainer');
  if (quoteStepsContainer) {
    initQuoteCalculator();
  }

  function initQuoteCalculator() {
    const totalSteps = 4;

    // Load pricing data from window (injected by Blade) or fallback
    const pricingCategories = window.pricingData || [];
    const quoteConfig = window.quoteConfig || {};
    const timelineMultipliers = quoteConfig.timeline_multipliers || {
      '1month': { min: 1.3, max: 1.5 },
      '1-3months': { min: 1.0, max: 1.0 },
      '3-6months': { min: 0.9, max: 0.95 },
      'flexible': { min: 0.85, max: 0.9 }
    };
    const timelineLabels = quoteConfig.timeline_labels || {
      '1month': '1 個月內', '1-3months': '1 - 3 個月',
      '3-6months': '3 - 6 個月', 'flexible': '彈性'
    };
    const budgetLabels = quoteConfig.budget_labels || {
      'under5': '5 萬以下', '5-15': '5 - 15 萬',
      '15-30': '15 - 30 萬', '30plus': '30 萬以上'
    };

    // State
    const state = {
      projectType: null,
      selectedCategory: null,
      features: [], // Array of feature objects { id, name, slug, price_min, price_max }
      timeline: null,
      budget: null,
      name: '',
      email: '',
      phone: '',
      company: '',
      message: ''
    };

    // Helper: find category by slug
    function findCategory(slug) {
      return pricingCategories.find(c => c.slug === slug) || null;
    }

    // Elements
    const progressFill = document.getElementById('quoteProgressFill');
    const progressSteps = document.querySelectorAll('.quote-progress-step');
    const steps = {
      1: document.getElementById('quoteStep1'),
      2: document.getElementById('quoteStep2'),
      3: document.getElementById('quoteStep3'),
      4: document.getElementById('quoteStep4'),
      result: document.getElementById('quoteResult')
    };

    // Step 1: Project type selection
    const step1Cards = steps[1].querySelectorAll('.quote-option-card');
    const btnStep1Next = document.getElementById('btnStep1Next');

    step1Cards.forEach(card => {
      card.addEventListener('click', () => {
        step1Cards.forEach(c => c.classList.remove('selected'));
        card.classList.add('selected');
        card.querySelector('input').checked = true;
        state.projectType = card.dataset.value;
        state.selectedCategory = findCategory(state.projectType);
        state.features = []; // Reset features when type changes
        btnStep1Next.disabled = false;
      });
    });

    btnStep1Next.addEventListener('click', () => {
      if (!state.projectType) return;
      populateFeatures(state.projectType);
      goToStep(2);
    });

    // Step 2: Features (dynamic from API data)
    const featuresGrid = document.getElementById('quoteFeaturesGrid');
    const btnStep2Prev = document.getElementById('btnStep2Prev');
    const btnStep2Next = document.getElementById('btnStep2Next');

    function populateFeatures(typeSlug) {
      const category = findCategory(typeSlug);
      const features = category ? category.features : [];
      featuresGrid.innerHTML = '';

      features.forEach(feat => {
        const label = document.createElement('label');
        label.className = 'quote-option-card quote-option-card--compact';
        label.dataset.value = feat.slug;
        label.dataset.featureId = feat.id;
        label.innerHTML = `
          <input type="checkbox" name="features" value="${feat.slug}">
          <div class="quote-option-inner">
            <h3 class="quote-option-title">${feat.name}</h3>
            <p class="quote-option-desc">${feat.description || ''}</p>
          </div>
          <span class="quote-option-check">
            <svg viewBox="0 0 24 24" width="18" height="18"><polyline points="20 6 9 17 4 12" fill="none" stroke="currentColor" stroke-width="2"/></svg>
          </span>
        `;

        label.addEventListener('click', (e) => {
          e.preventDefault();
          const checkbox = label.querySelector('input');
          checkbox.checked = !checkbox.checked;
          label.classList.toggle('selected', checkbox.checked);

          if (checkbox.checked) {
            if (!state.features.find(f => f.id === feat.id)) {
              state.features.push({
                id: feat.id,
                name: feat.name,
                slug: feat.slug,
                price_min: feat.price_min,
                price_max: feat.price_max
              });
            }
          } else {
            state.features = state.features.filter(f => f.id !== feat.id);
          }
        });

        // Restore previous selection
        if (state.features.find(f => f.id === feat.id)) {
          label.classList.add('selected');
          label.querySelector('input').checked = true;
        }

        featuresGrid.appendChild(label);
      });
    }

    btnStep2Prev.addEventListener('click', () => goToStep(1));
    btnStep2Next.addEventListener('click', () => goToStep(3));

    // Step 3: Timeline and Budget
    const btnStep3Prev = document.getElementById('btnStep3Prev');
    const btnStep3Next = document.getElementById('btnStep3Next');

    function setupRadioCards(container, radioName, stateKey) {
      const cards = container.querySelectorAll(`input[name="${radioName}"]`);
      cards.forEach(input => {
        const card = input.closest('.quote-option-card');
        card.addEventListener('click', () => {
          const siblings = container.querySelectorAll(`input[name="${radioName}"]`);
          siblings.forEach(s => s.closest('.quote-option-card').classList.remove('selected'));
          card.classList.add('selected');
          input.checked = true;
          state[stateKey] = input.value;
          validateStep3();
        });

        if (state[stateKey] === input.value) {
          card.classList.add('selected');
          input.checked = true;
        }
      });
    }

    setupRadioCards(steps[3], 'timeline', 'timeline');
    setupRadioCards(steps[3], 'budget', 'budget');

    function validateStep3() {
      btnStep3Next.disabled = !(state.timeline && state.budget);
    }

    btnStep3Prev.addEventListener('click', () => goToStep(2));
    btnStep3Next.addEventListener('click', () => {
      if (!state.timeline || !state.budget) return;
      goToStep(4);
    });

    // Step 4: Contact info
    const btnStep4Prev = document.getElementById('btnStep4Prev');
    const btnCalculate = document.getElementById('btnCalculate');
    const nameInput = document.getElementById('quoteName');
    const emailInput = document.getElementById('quoteEmail');
    const phoneInput = document.getElementById('quotePhone');
    const companyInput = document.getElementById('quoteCompany');
    const messageInput = document.getElementById('quoteMessage');

    function validateStep4() {
      const nameVal = nameInput.value.trim();
      const emailVal = emailInput.value.trim();
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      btnCalculate.disabled = !(nameVal && emailVal && emailRegex.test(emailVal));
    }

    nameInput.addEventListener('input', () => {
      state.name = nameInput.value.trim();
      nameInput.classList.remove('error');
      validateStep4();
    });
    emailInput.addEventListener('input', () => {
      state.email = emailInput.value.trim();
      emailInput.classList.remove('error');
      validateStep4();
    });
    if (phoneInput) {
      phoneInput.addEventListener('input', () => {
        state.phone = phoneInput.value.trim();
      });
    }
    companyInput.addEventListener('input', () => {
      state.company = companyInput.value.trim();
    });
    messageInput.addEventListener('input', () => {
      state.message = messageInput.value.trim();
    });

    btnStep4Prev.addEventListener('click', () => goToStep(3));
    btnCalculate.addEventListener('click', () => {
      let valid = true;
      if (!state.name) { nameInput.classList.add('error'); valid = false; }
      if (!state.email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(state.email)) { emailInput.classList.add('error'); valid = false; }
      if (!valid) return;

      calculateAndShowResult();
    });

    // URL 預選類別
    const preselected = window.preselectedCategory;
    if (preselected && pricingCategories) {
      const matchingCard = steps[1].querySelector(`.quote-option-card[data-value="${preselected}"]`);
      if (matchingCard) {
        matchingCard.click();
        setTimeout(() => {
          if (btnStep1Next && !btnStep1Next.disabled) {
            btnStep1Next.click();
          }
        }, 400);
      }
    }

    // Reset
    const btnReset = document.getElementById('btnReset');
    btnReset.addEventListener('click', () => {
      state.projectType = null;
      state.selectedCategory = null;
      state.features = [];
      state.timeline = null;
      state.budget = null;
      state.name = '';
      state.email = '';
      state.phone = '';
      state.company = '';
      state.message = '';

      step1Cards.forEach(c => { c.classList.remove('selected'); c.querySelector('input').checked = false; });
      btnStep1Next.disabled = true;

      steps[3].querySelectorAll('.quote-option-card').forEach(c => {
        c.classList.remove('selected');
        const input = c.querySelector('input');
        if (input) input.checked = false;
      });
      btnStep3Next.disabled = true;

      nameInput.value = '';
      emailInput.value = '';
      if (phoneInput) phoneInput.value = '';
      companyInput.value = '';
      messageInput.value = '';
      nameInput.classList.remove('error');
      emailInput.classList.remove('error');
      btnCalculate.disabled = true;

      goToStep(1);
    });

    // Navigation
    function goToStep(step) {
      Object.values(steps).forEach(el => el.classList.remove('active'));

      if (step === 'result') {
        steps.result.classList.add('active');
        updateProgress(totalSteps);
      } else {
        steps[step].classList.add('active');
        updateProgress(step);
      }

      const quoteSection = document.querySelector('.quote-section');
      if (quoteSection) {
        quoteSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    }

    function updateProgress(step) {
      const percent = (step / totalSteps) * 100;
      progressFill.style.width = percent + '%';

      progressSteps.forEach((el, idx) => {
        el.classList.remove('active', 'completed');
        if (idx + 1 < step) {
          el.classList.add('completed');
        } else if (idx + 1 === step) {
          el.classList.add('active');
        }
      });
    }

    // Calculate — uses per-feature pricing from DB data
    function calculateAndShowResult() {
      const category = state.selectedCategory;
      const timeline = state.timeline;

      // Base price from category
      const baseMin = category ? category.base_price_min : 30000;
      const baseMax = category ? category.base_price_max : 80000;

      // Sum individual feature prices
      let featuresMinSum = 0;
      let featuresMaxSum = 0;
      state.features.forEach(f => {
        featuresMinSum += f.price_min;
        featuresMaxSum += f.price_max;
      });

      // Timeline multiplier
      const multiplier = timelineMultipliers[timeline] || { min: 1.0, max: 1.0 };

      let minPrice = (baseMin + featuresMinSum) * multiplier.min;
      let maxPrice = (baseMax + featuresMaxSum) * multiplier.max;

      // Round to nearest thousand
      minPrice = Math.round(minPrice / 1000) * 1000;
      maxPrice = Math.round(maxPrice / 1000) * 1000;

      const formatPrice = (n) => 'NT$ ' + n.toLocaleString('zh-TW');

      document.getElementById('quoteEstimatePrice').textContent =
        formatPrice(minPrice) + ' ~ ' + formatPrice(maxPrice);

      // Summary
      const featureNames = state.features.map(f => f.name);
      const categoryName = category ? category.name : state.projectType;

      const summaryEl = document.getElementById('quoteResultSummary');
      summaryEl.innerHTML = `
        <div class="quote-summary-item">
          <div class="quote-summary-label">專案類型</div>
          <div class="quote-summary-value">${categoryName}</div>
        </div>
        <div class="quote-summary-item">
          <div class="quote-summary-label">功能模組</div>
          <div class="quote-summary-value">${featureNames.length > 0 ? featureNames.join('、') : '未選擇'}</div>
        </div>
        <div class="quote-summary-item">
          <div class="quote-summary-label">專案時程</div>
          <div class="quote-summary-value">${timelineLabels[timeline] || timeline}</div>
        </div>
        <div class="quote-summary-item">
          <div class="quote-summary-label">預算範圍</div>
          <div class="quote-summary-value">${budgetLabels[state.budget] || state.budget}</div>
        </div>
      `;

      // Populate hidden form fields for POST to /quote-request
      document.getElementById('quoteSubmitName').value = state.name;
      document.getElementById('quoteSubmitEmail').value = state.email;
      document.getElementById('quoteSubmitPhone').value = state.phone;
      document.getElementById('quoteSubmitCompany').value = state.company;
      document.getElementById('quoteSubmitProjectType').value = category ? category.slug : state.projectType;
      document.getElementById('quoteSubmitTimeline').value = state.timeline;
      document.getElementById('quoteSubmitBudget').value = state.budget;
      document.getElementById('quoteSubmitEstimatedMin').value = minPrice;
      document.getElementById('quoteSubmitEstimatedMax').value = maxPrice;
      document.getElementById('quoteSubmitMessage').value = state.message;

      // Selected features as JSON array
      const featuresPayload = state.features.map(f => ({
        id: f.id,
        name: f.name,
        slug: f.slug,
        price_min: f.price_min,
        price_max: f.price_max
      }));
      document.getElementById('quoteSubmitFeatures').value = JSON.stringify(featuresPayload);

      goToStep('result');
    }
  }

  // ===== Theme Toggle (dark/light) with localStorage =====
  const savedTheme = localStorage.getItem('mh-theme');
  if (savedTheme === 'light') {
    document.body.classList.add('light-theme');
  }

  // Create theme toggle button
  const themeToggle = document.createElement('button');
  themeToggle.className = 'theme-toggle';
  themeToggle.setAttribute('aria-label', 'Toggle theme');
  themeToggle.innerHTML = document.body.classList.contains('light-theme')
    ? '<svg viewBox="0 0 24 24"><path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/></svg>'
    : '<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>';
  document.body.appendChild(themeToggle);

  themeToggle.addEventListener('click', () => {
    const isLight = document.body.classList.toggle('light-theme');
    localStorage.setItem('mh-theme', isLight ? 'light' : 'dark');

    // Update icon
    themeToggle.innerHTML = isLight
      ? '<svg viewBox="0 0 24 24"><path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/></svg>'
      : '<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>';
  });

  // ===== Pricing Plan CTA — Scroll to Calculator =====
  document.querySelectorAll('[data-scroll-to-calculator]').forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      const calculator = document.getElementById('quoteStepsContainer');
      if (calculator) {
        calculator.scrollIntoView({ behavior: 'smooth', block: 'start' });
        // Auto-select "web" category if available
        setTimeout(() => {
          const webOption = document.querySelector('.quote-option-card[data-value="web"]');
          if (webOption && !webOption.querySelector('input:checked')) {
            webOption.click();
          }
        }, 600);
      }
    });
  });
});
