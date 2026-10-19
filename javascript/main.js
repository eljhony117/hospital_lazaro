document.addEventListener('DOMContentLoaded', () => {
  const $navbarBurgers = Array.prototype.slice.call(document.querySelectorAll('.navbar-burger'), 0);

  if ($navbarBurgers.length > 0) {
    $navbarBurgers.forEach( el => {
      el.addEventListener('click', () => {
        const target = el.dataset.target;
        const $target = document.getElementById(target);
        el.classList.toggle('is-active');
        $target.classList.toggle('is-active');
      });
    });
  }
});


document.addEventListener('DOMContentLoaded', () => {
  // Buscamos los elementos que nombramos en el Paso 1
  const linkMas = document.getElementById('link-mas');
  const dropdownMas = document.getElementById('dropdown-mas');

  // Si existen en la página, le agregamos el evento de clic
  if (linkMas && dropdownMas) {
    linkMas.addEventListener('click', (evento) => {
      evento.preventDefault(); // Evita que la página salte hacia arriba al tocar el link
      
      // Activa o desactiva la clase que creamos en el CSS
      dropdownMas.classList.toggle('is-open-mobile');
    });
  }
});