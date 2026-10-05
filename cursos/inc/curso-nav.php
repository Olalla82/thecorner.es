<style>
.site-header{position:sticky;top:0;background:#fff;border-bottom:1px solid #e5e7eb;z-index:1000;box-shadow:0 2px 4px rgba(0,0,0,.05)}
.site-header .header-inner{display:flex;justify-content:space-between;align-items:center;gap:24px;max-width:1240px;min-height:80px;height:auto;margin:0 auto;padding:15px 20px}
.site-header .brand{display:block;flex-shrink:0}
.site-header .nav-menu{display:flex;align-items:center;gap:32px}
.site-header .nav-menu__item{position:relative;font-size:15px;font-weight:500;color:#333;cursor:pointer;text-decoration:none}
.site-header .nav-menu__item:hover,.site-header .nav-menu__item:focus-visible{color:#C2D500}
.site-header .nav-menu__item--dropdown{display:flex;align-items:center;gap:6px}
.site-header .nav-menu__item--dropdown::after{content:'\25BE';font-size:10px}
.site-header .nav-dropdown{position:absolute;top:100%;left:0;min-width:220px;margin-top:12px;padding:12px 0;background:#fff;border-radius:8px;box-shadow:0 8px 24px rgba(0,0,0,.12);opacity:0;visibility:hidden;transform:translateY(-10px);transition:opacity .2s,transform .2s}
.site-header .nav-dropdown::before{content:'';position:absolute;bottom:100%;left:0;width:100%;height:12px}
.site-header .lang-selector-menu .nav-dropdown{left:auto;right:0}
.site-header .nav-menu__item:hover .nav-dropdown,.site-header .nav-menu__item:focus-within .nav-dropdown{opacity:1;visibility:visible;transform:none}
.site-header .nav-dropdown__item{display:block;padding:12px 24px;font-size:14px;color:#333;text-decoration:none;line-height:1.4}
.site-header .nav-dropdown__item:hover,.site-header .nav-dropdown__item:focus-visible{background:#f8fafc;color:#859200}
.site-header .nav-dropdown__item.active-lang{background:#C2D500;color:#1f2937;font-weight:600}
.site-header .mobile-menu-toggle{display:none;flex-direction:column;justify-content:center;align-items:center;gap:5px;width:44px;height:44px;flex-shrink:0;padding:8px;border:0;border-radius:4px;background:transparent;cursor:pointer}
.site-header .mobile-menu-toggle span{display:block;width:25px;height:3px;background:#333;border-radius:2px}
.site-header :focus-visible{outline:2px solid #859200;outline-offset:4px}
@media(max-width:1024px){
  .site-header .header-inner{min-height:72px;padding:11px 20px}
  .site-header .brand img{max-width:calc(100vw - 108px);height:auto!important;max-height:50px;object-fit:contain}
  .site-header .mobile-menu-toggle{display:flex}
  .site-header .nav-menu{display:none;position:absolute;top:100%;left:0;right:0;max-height:calc(100dvh - 72px);overflow-y:auto;padding:16px 20px 24px;background:#fff;border-bottom:1px solid #e5e7eb;box-shadow:0 8px 16px rgba(0,0,0,.08)}
  .site-header .nav-menu.is-open{display:flex;flex-direction:column;align-items:stretch;gap:0}
  .site-header .nav-menu__item{padding:12px 0;line-height:1.5;overflow-wrap:anywhere}
  .site-header .nav-menu__item--dropdown{display:block}
  .site-header .nav-menu__item--dropdown::after{display:none}
  .site-header .nav-dropdown,.site-header .lang-selector-menu .nav-dropdown{position:static;min-width:0!important;margin:8px 0 0;padding:0;border-radius:0;box-shadow:none;opacity:1;visibility:visible;transform:none}
  .site-header .nav-dropdown__item{padding:10px 16px}
  .site-header .nav-dropdown::before{display:none}
}
</style>
<script>
document.addEventListener('DOMContentLoaded', function () {
  const header = document.querySelector('.site-header');
  const toggle = header.querySelector('.mobile-menu-toggle');
  const menu = header.querySelector('.nav-menu');
  const mobile = window.matchMedia('(max-width:1024px)');
  function setOpen(open) {
    menu.classList.toggle('is-open', open);
    toggle.setAttribute('aria-expanded', String(open));
    toggle.setAttribute('aria-label', open ? '<?= $courseNavLocale === 'ca' ? 'Tancar el menu' : 'Cerrar el menu' ?>' : '<?= $courseNavLocale === 'ca' ? 'Obrir el menu' : 'Abrir el menu' ?>');
  }
  toggle.addEventListener('click', function () { setOpen(!menu.classList.contains('is-open')); });
  document.addEventListener('click', function (event) {
    if (!header.contains(event.target)) setOpen(false);
  });
  document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape' && menu.classList.contains('is-open')) {
      setOpen(false);
      toggle.focus();
    }
  });
  menu.addEventListener('click', function (event) {
    if (mobile.matches && event.target.closest('a')) setOpen(false);
  });
  mobile.addEventListener('change', function () { setOpen(false); });
  header.querySelectorAll('.nav-menu__item--dropdown').forEach(function (item) {
    item.tabIndex = 0;
  });
});
</script>
