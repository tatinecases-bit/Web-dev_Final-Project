document.querySelectorAll('form[data-confirm]').forEach(function (f) {
  f.addEventListener('submit', function (e) {
    if (!confirm(f.dataset.confirm)) e.preventDefault();
  });
});
document.querySelectorAll('[data-mark-all]').forEach(function (b) {
  b.addEventListener('click', function () {
    document.querySelectorAll('input[type=radio][value="' + b.dataset.markAll + '"]').forEach(function (r) { r.checked = true; });
  });
});
document.querySelectorAll('input[data-filter]').forEach(function (inp) {
  inp.addEventListener('input', function () {
    var q = inp.value.toLowerCase();
    document.querySelectorAll('#' + inp.dataset.filter + ' tr').forEach(function (tr, i) {
      if (i === 0) return;
      tr.style.display = tr.textContent.toLowerCase().indexOf(q) === -1 ? 'none' : '';
    });
  });
});
