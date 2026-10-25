// Minimal hash router: loads HTML templates from views/ into #app without page reloads.
// Static milestone (M1): no backend calls. Needs a local web server (fetch does not work on file://).
const routes = {
  home: 'Home', login: 'Log in', register: 'Register', vehicles: 'Vehicles',
  vehicle: 'Vehicle details', dashboard: 'My dashboard', profile: 'Profile', admin: 'Admin'
};
const app = document.getElementById('app');

async function loadRoute() {
  let name = location.hash.replace(/^#\//, '').split('?')[0] || 'home';
  if (!routes[name]) name = 'home';
  try {
    const res = await fetch(`views/${name}.html`);
    if (!res.ok) throw new Error(res.status);
    app.innerHTML = await res.text();
  } catch (e) {
    app.innerHTML = '<div class="container page"><div class="alert alert-danger">Could not load this page. Serve the frontend folder with a local web server (e.g. <code>python -m http.server</code>) instead of opening the file directly.</div></div>';
  }
  document.title = `${routes[name]} | Redline Autos`;
  document.querySelectorAll('#nav .nav-link').forEach(a =>
    a.classList.toggle('active', a.getAttribute('href') === `#/${name}`));
  bootstrap.Collapse.getInstance(document.getElementById('nav'))?.hide();
  window.scrollTo(0, 0);
}

app.addEventListener('submit', e => e.preventDefault()); // forms are static until M4
window.addEventListener('hashchange', loadRoute);
loadRoute();
