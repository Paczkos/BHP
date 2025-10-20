const languageSwitcher = document.querySelectorAll('[data-lang]');
const darkToggle = document.querySelector('#dark-mode-toggle');

languageSwitcher.forEach(btn => {
  btn.addEventListener('click', (event) => {
    event.preventDefault();
    const lang = btn.dataset.lang;
    const url = new URL(window.location.href);
    url.searchParams.set('lang', lang);
    window.location.href = url.toString();
  });
});

if (darkToggle) {
  const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
  const stored = localStorage.getItem('bhp-dark-mode');
  const isDark = stored ? stored === 'true' : prefersDark;

  toggleDarkMode(isDark);
  darkToggle.checked = isDark;

  darkToggle.addEventListener('change', (event) => {
    toggleDarkMode(event.target.checked);
    localStorage.setItem('bhp-dark-mode', event.target.checked);
  });
}

function toggleDarkMode(enable) {
  document.documentElement.classList.toggle('dark', enable);
  if (enable) {
    document.body.style.background = '#0f172a';
    document.body.style.color = '#f8fafc';
  } else {
    document.body.style.background = '';
    document.body.style.color = '';
  }
}

document.querySelectorAll('form[data-confirm]').forEach((form) => {
  form.addEventListener('submit', (event) => {
    const message = form.dataset.confirm;
    if (message && !window.confirm(message)) {
      event.preventDefault();
    }
  });
});

document.querySelectorAll('.answer-group').forEach((group) => {
  const inputs = group.querySelectorAll("input[type='radio']");
  inputs.forEach((input) => {
    const option = input.closest('.answer-option');
    if (!option) {
      return;
    }

    if (input.checked) {
      option.classList.add('selected');
    }

    input.addEventListener('change', () => {
      inputs.forEach((peer) => {
        const peerOption = peer.closest('.answer-option');
        if (peerOption) {
          peerOption.classList.remove('selected');
        }
      });
      option.classList.add('selected');
    });
  });
});
