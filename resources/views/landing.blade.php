<!DOCTYPE html>
<html lang="es-MX">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="theme-color" content="#1150ed">
  <title>tapio — Un toque y listo</title>
  <meta name="description" content="tapio convierte un stand NFC en el menú, las reseñas, las encuestas y las redes de tu negocio. Un toque y listo. Sin apps, sin descargas.">

  {{-- No-flash theme resolution (shared with the panel: localStorage + cookie) --}}
  @include('partials.theme-init')

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

  @vite(['resources/css/landing.css'])
</head>

<body>

  @php
  $wa = 'https://wa.me/52XXXXXXXXXX?text=' . rawurlencode('Hola, quiero una demo de tapio');
  $logoBlue = asset('img/tapio-logo-blue.png');
  $logoWhite = asset('img/tapio-logo-white.png');
  @endphp

  {{-- HEADER --}}
  <header id="top">
    <div class="wrap nav">
      <a href="#top"><img class="logo" src="{{ $logoBlue }}" alt="tapio"></a>
      <nav class="nav-links">
        <a href="#que-es">¿Qué es?</a>
        <a href="#como">Cómo funciona</a>
        <a href="#casos">Casos de uso</a>
        <a href="#precios">Precios</a>
      </nav>
      <div class="nav-cta">
        <button class="theme-toggle" data-theme-toggle type="button" aria-label="Cambiar tema claro/oscuro">
          <svg class="i-light" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z" />
          </svg>
          <svg class="i-dark" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="5" />
            <line x1="12" y1="1" x2="12" y2="3" />
            <line x1="12" y1="21" x2="12" y2="23" />
            <line x1="4.22" y1="4.22" x2="5.64" y2="5.64" />
            <line x1="18.36" y1="18.36" x2="19.78" y2="19.78" />
            <line x1="1" y1="12" x2="3" y2="12" />
            <line x1="21" y1="12" x2="23" y2="12" />
            <line x1="4.22" y1="19.78" x2="5.64" y2="18.36" />
            <line x1="18.36" y1="5.64" x2="19.78" y2="4.22" />
          </svg>
        </button>
        <a href="{{ url('/login') }}" class="btn btn-ghost panel-btn">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4" />
            <polyline points="10 17 15 12 10 7" />
            <line x1="15" y1="12" x2="3" y2="12" />
          </svg>
          Panel
        </a>
        <a href="{{ $wa }}" class="btn btn-primary desktop-cta">Solicita tu demo</a>
        <button class="menu-btn" id="menuBtn" aria-label="Abrir menú"><span></span><span></span><span></span></button>
      </div>
    </div>
  </header>

  <div class="mobile-nav" id="mobileNav" aria-hidden="true">
    <aside class="mnav-panel" role="dialog" aria-modal="true" aria-label="Menú">
      <div class="mnav-top">
        <img class="brand-logo" src="{{ $logoBlue }}" alt="tapio">
        <button class="mnav-close" id="closeNav" aria-label="Cerrar menú"><i class="fas fa-xmark"></i></button>
      </div>

      <nav class="mnav-links">
        <a class="mnav-link" href="#que-es"><span>¿Qué es?</span><i class="fas fa-chevron-right"></i></a>
        <a class="mnav-link" href="#como"><span>Cómo funciona</span><i class="fas fa-chevron-right"></i></a>
        <a class="mnav-link" href="#casos"><span>Casos de uso</span><i class="fas fa-chevron-right"></i></a>
        <a class="mnav-link" href="#precios"><span>Precios</span><i class="fas fa-chevron-right"></i></a>
        <a class="mnav-link" href="{{ url('/login') }}"><span>Entrar al panel</span><i class="fas fa-arrow-right-to-bracket"></i></a>
      </nav>

      <div class="mnav-foot">
        <a href="{{ $wa }}" class="btn btn-primary mnav-cta"><i class="fab fa-whatsapp"></i> Solicita tu demo</a>
        <div class="mnav-social">
          <a href="https://instagram.com/tapio.mx" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
          <a href="{{ $wa }}" aria-label="WhatsApp"><i class="fab fa-whatsapp"></i></a>
          <a href="https://facebook.com/tapio.mx" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
          <a href="https://tiktok.com/@tapio.mx" aria-label="TikTok"><i class="fab fa-tiktok"></i></a>
        </div>
        <div class="mnav-meta">
          <a href="mailto:hola@tapio.mx"><i class="fas fa-envelope"></i> hola@tapio.mx</a>
          <span>© {{ date('Y') }} tapio · Un toque y listo.</span>
        </div>
      </div>
    </aside>
  </div>

  {{-- HERO --}}
  <section class="hero">
    <div class="hero-arcs"></div>
    <div class="wrap">
      <div class="hero-copy">
        <span class="eyebrow">Stands NFC inteligentes</span>
        <h1>Un toque<br>y <span class="accent">listo.</span></h1>
        <p class="lead">Tu menú, tus reseñas, tus encuestas y tus redes en un solo stand. El cliente acerca su teléfono y ya está — sin apps, sin descargas, sin códigos que escanear.</p>
        <div class="hero-cta">
          <a href="{{ $wa }}" class="btn btn-primary">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
              <path d="M.057 24l1.687-6.163a11.867 11.867 0 01-1.587-5.945C.16 5.335 5.495 0 12.05 0a11.817 11.817 0 018.413 3.488 11.824 11.824 0 013.48 8.414c-.003 6.557-5.338 11.892-11.893 11.892a11.9 11.9 0 01-5.688-1.448L.057 24zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884a9.82 9.82 0 001.516 5.26l-.999 3.648 3.472-.91z" />
            </svg>
            Solicita tu demo
          </a>
          <a href="#como" class="btn btn-ghost">Ver cómo funciona</a>
        </div>
        <p class="hero-note">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#1150ed" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="20 6 9 17 4 12" />
          </svg>
          El destino se edita desde tu panel, cuando quieras.
        </p>
      </div>
      <div class="stage" aria-hidden="true">
        <div class="phone">
          <div class="phone-notch"></div>
          <div class="phone-screen">
            <img class="phone-logo" src="{{ $logoBlue }}" alt="">
            <div class="phone-menu">
              <div class="pm-row"><span class="pm-dot"></span><span class="pm-line" style="width:64%"></span></div>
              <div class="pm-row"><span class="pm-dot"></span><span class="pm-line" style="width:48%"></span></div>
              <div class="pm-row"><span class="pm-dot"></span><span class="pm-line" style="width:70%"></span></div>
              <div class="pm-row"><span class="pm-dot"></span><span class="pm-line" style="width:40%"></span></div>
            </div>
          </div>
        </div>
        <div class="stand">
          <img src="{{ $logoBlue }}" alt="">
          <svg class="nfc" viewBox="0 0 24 24" fill="none" stroke="#1150ed" stroke-width="1.7" stroke-linecap="round">
            <path d="M5 9a9 9 0 0114 0M8 12a5 5 0 018 0M11 15a1.5 1.5 0 012 0" />
          </svg>
        </div>
        <div class="nfc-wave"><span></span><span></span><span></span></div>
      </div>
    </div>
  </section>

  {{-- TRUST STRIP --}}
  <div class="strip">
    <div class="wrap">Compatible con <b>cualquier teléfono con NFC</b> &nbsp;·&nbsp; Sin instalar nada &nbsp;·&nbsp; Español de México &nbsp;·&nbsp; Panel de control incluido</div>
  </div>

  {{-- WHAT IS IT --}}
  <section id="que-es">
    <div class="wrap">
      <div class="sec-head reveal">
        <span class="eyebrow">Un stand, todo lo que necesitas</span>
        <h2>Un toque, destinos infinitos</h2>
        <p class="lead">Cada stand tapio guarda un enlace permanente que tú controlas. Decide a dónde lleva y cámbialo cuando quieras — el stand nunca cambia.</p>
      </div>
      <div class="grid">
        <div class="card reveal">
          <div class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M3 2h13v20l-6.5-3L3 22z" />
              <path d="M19 2v16" />
            </svg></div>
          <h3>Menú digital</h3>
          <p>Tu carta siempre actualizada, sin reimprimir. Cambia platillos y precios en segundos.</p>
        </div>
        <div class="card reveal">
          <div class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <polygon points="12 2 15 9 22 9 16.5 13.5 18.5 21 12 16.5 5.5 21 7.5 13.5 2 9 9 9" />
            </svg></div>
          <h3>Reseñas de Google</h3>
          <p>Lleva a tus clientes directo a dejar 5 estrellas y sube tu reputación en el mapa.</p>
        </div>
        <div class="card reveal">
          <div class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M9 11l3 3L22 4" />
              <path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11" />
            </svg></div>
          <h3>Encuestas</h3>
          <p>Mide la experiencia y captura datos de contacto al momento, con reportes en tu panel.</p>
        </div>
        <div class="card reveal">
          <div class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="4" />
              <circle cx="19" cy="5" r="2" />
              <circle cx="5" cy="19" r="2" />
              <path d="M14.5 9.5l3-3M9.5 14.5l-3 3" />
            </svg></div>
          <h3>Redes sociales</h3>
          <p>Instagram, TikTok, WhatsApp y más en una página linktree con tu marca.</p>
        </div>
        <div class="card reveal">
          <div class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M5 12.55a11 11 0 0114 0M1.5 9a16 16 0 0121 0M8.5 16a6 6 0 017 0" />
              <line x1="12" y1="20" x2="12.01" y2="20" />
            </svg></div>
          <h3>WiFi al instante</h3>
          <p>Conecta a tus clientes a tu red con un toque — sin dictar contraseñas.</p>
        </div>
        <div class="card reveal">
          <div class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M10 13a5 5 0 007 0l3-3a5 5 0 00-7-7l-1 1" />
              <path d="M14 11a5 5 0 00-7 0l-3 3a5 5 0 007 7l1-1" />
            </svg></div>
          <h3>Enlace directo</h3>
          <p>¿Una promo, un formulario, tu tienda en línea? Apunta el stand a donde tú decidas.</p>
        </div>
      </div>
    </div>
  </section>

  {{-- HOW IT WORKS --}}
  <section id="como" class="how">
    <div class="wrap">
      <div class="sec-head reveal">
        <span class="eyebrow">Así de simple</span>
        <h2>Cómo funciona</h2>
        <p class="lead" style="color:#aeb4d6">Listo para usarse desde el primer día. Sin técnicos, sin complicaciones.</p>
      </div>
      <div class="steps">
        <div class="step reveal">
          <h3>Coloca tu stand</h3>
          <p>Lo recibes listo y con tu marca. Ponlo en la mesa, la barra o la entrada.</p>
        </div>
        <div class="step reveal">
          <h3>El cliente acerca su teléfono</h3>
          <p>Un toque y se abre tu contenido al instante. Sin apps, sin escanear, sin fricción.</p>
        </div>
        <div class="step reveal">
          <h3>Tú controlas el destino</h3>
          <p>Desde tu panel cambias a dónde lleva cada stand y ves las estadísticas de escaneos.</p>
        </div>
      </div>
      <div class="how-foot reveal">
        <span class="pill">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M23 4v6h-6M1 20v-6h6" />
            <path d="M3.51 9a9 9 0 0114.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0020.49 15" />
          </svg>
          Enlaces dinámicos: el stand nunca cambia, el destino sí.
        </span>
      </div>
    </div>
  </section>

  {{-- USE CASES --}}
  <section id="casos" class="cases">
    <div class="wrap">
      <div class="sec-head reveal">
        <span class="eyebrow">Hecho para hospitalidad</span>
        <h2>Para cada tipo de negocio</h2>
      </div>
      <div class="grid">
        <div class="case reveal">
          <div class="emoji"><i class="fas fa-utensils accent"></i></div>
          <h3>Restaurantes</h3>
          <p>Menú, reseñas y encuestas en la mesa.</p>
        </div>
        <div class="case reveal">
          <div class="emoji"><i class="fas fa-coffee accent"></i></div>
          <h3>Cafés</h3>
          <p>Carta, WiFi y redes en la barra.</p>
        </div>
        <div class="case reveal">
          <div class="emoji"><i class="fas fa-martini-glass-citrus accent"></i></div>
          <h3>Bares</h3>
          <p>Promos y playlist con un toque.</p>
        </div>
        <div class="case reveal">
          <div class="emoji"><i class="fas fa-bell-concierge accent"></i></div>
          <h3>Hoteles</h3>
          <p>Servicios, WiFi y guía local en recepción.</p>
        </div>
      </div>
    </div>
  </section>

  {{-- METRICS --}}
  <section class="metrics">
    <div class="wrap">
      <div class="grid">
        <div class="metric reveal">
          <div class="n">0</div>
          <p>apps que descargar</p>
        </div>
        <div class="metric reveal">
          <div class="n">1</div>
          <p>toque para conectar</p>
        </div>
        <div class="metric reveal">
          <div class="n">∞</div>
          <p>veces que puedes cambiar el destino</p>
        </div>
      </div>
    </div>
  </section>

  {{-- PRICING --}}
  <section id="precios" class="pricing">
    <div class="wrap">
      <div class="sec-head reveal">
        <span class="eyebrow">Planes</span>
        <h2>Precios claros, sin sorpresas</h2>
        <p class="lead">Elige el plan que va con tu negocio. Todos incluyen panel de control y soporte en español.</p>
      </div>
      <div class="plans">
        <div class="plan reveal">
          <h3>Esencial</h3>
          <p class="desc">Para un solo local que empieza.</p>
          <div class="price">$XXX<small> MXN/mes</small></div>
          <ul>
            <li><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="20 6 9 17 4 12" />
              </svg>1 stand NFC con tu marca</li>
            <li><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="20 6 9 17 4 12" />
              </svg>Menú, reseñas y enlaces</li>
            <li><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="20 6 9 17 4 12" />
              </svg>Panel de control</li>
            <li><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="20 6 9 17 4 12" />
              </svg>Estadísticas de escaneos</li>
          </ul>
          <a href="{{ $wa }}" class="btn btn-ghost">Empezar</a>
        </div>
        <div class="plan featured reveal">
          <span class="tag">Más popular</span>
          <h3>Negocio</h3>
          <p class="desc">Para locales que quieren todo el potencial.</p>
          <div class="price">$XXX<small> MXN/mes</small></div>
          <ul>
            <li><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="20 6 9 17 4 12" />
              </svg>Hasta 5 stands NFC</li>
            <li><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="20 6 9 17 4 12" />
              </svg>Encuestas + captura de contactos</li>
            <li><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="20 6 9 17 4 12" />
              </svg>WiFi NFC y linktree de redes</li>
            <li><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="20 6 9 17 4 12" />
              </svg>Marca personalizada completa</li>
          </ul>
          <a href="{{ $wa }}" class="btn btn-primary">Solicita tu demo</a>
        </div>
        <div class="plan reveal">
          <h3>Cadena</h3>
          <p class="desc">Para varios locales o resellers.</p>
          <div class="price">A medida</div>
          <ul>
            <li><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="20 6 9 17 4 12" />
              </svg>Stands ilimitados</li>
            <li><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="20 6 9 17 4 12" />
              </svg>Multi-sucursal en un panel</li>
            <li><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="20 6 9 17 4 12" />
              </svg>Marca blanca para reventa</li>
            <li><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="20 6 9 17 4 12" />
              </svg>Soporte prioritario</li>
          </ul>
          <a href="{{ $wa }}" class="btn btn-ghost">Contactar ventas</a>
        </div>
      </div>
      <p class="price-note">Precios de referencia — ajústalos a tu oferta real. ¿Dudas? Escríbenos y armamos un plan a tu medida.</p>
    </div>
  </section>

  {{-- FINAL CTA --}}
  <section class="final">
    <div class="wrap">
      <h2>¿Listo para tu primer toque?</h2>
      <p>Agenda una demo y te mostramos cómo tapio convierte cada mesa en una experiencia digital.</p>
      <div class="btns">
        <a href="{{ $wa }}" class="btn btn-light">Solicita tu demo</a>
        <a href="https://instagram.com/tapio.mx" class="btn btn-outline-light">@tapio.mx</a>
      </div>
    </div>
  </section>

  {{-- FOOTER --}}
  <footer>
    <div class="wrap">
      <div class="foot-top">
        <div class="foot-brand">
          <img class="foot-logo" src="{{ $logoWhite }}" alt="tapio">
          <p>Un toque y listo. Stands NFC inteligentes para restaurantes, cafés y hospitalidad en México.</p>
        </div>
        <div class="foot-cols">
          <div class="foot-col">
            <h4>Producto</h4>
            <a href="#que-es">¿Qué es?</a>
            <a href="#como">Cómo funciona</a>
            <a href="#casos">Casos de uso</a>
            <a href="#precios">Precios</a>
          </div>
          <div class="foot-col">
            <h4>Contacto</h4>
            <a href="{{ $wa }}">WhatsApp</a>
            <a href="https://instagram.com/tapio.mx">Instagram</a>
            <a href="mailto:hola@tapio.mx">hola@tapio.mx</a>
            <a href="{{ url('/login') }}">Entrar al panel</a>
          </div>
        </div>
      </div>
      <div class="foot-bot">
        <span>© {{ date('Y') }} tapio. Todos los derechos reservados.</span>
        <span class="made">tapio <b>×</b> Complemento</span>
      </div>
    </div>
  </footer>

  <script>
    // sticky header shadow
    (function() {
      var header = document.getElementById('top');
      var onScroll = function() {
        header.classList.toggle('scrolled', window.scrollY > 12)
      };
      onScroll();
      addEventListener('scroll', onScroll, {
        passive: true
      });

      // mobile nav — slide-in drawer
      var mb = document.getElementById('menuBtn'),
        mn = document.getElementById('mobileNav'),
        cn = document.getElementById('closeNav');

      function openNav() {
        mn.classList.add('open');
        mn.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
      }

      function closeNav() {
        mn.classList.remove('open');
        mn.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
      }
      mb.addEventListener('click', openNav);
      cn.addEventListener('click', closeNav);
      // tap on the dimmed backdrop (outside the panel) closes
      mn.addEventListener('click', function(e) {
        if (e.target === mn) closeNav();
      });
      mn.querySelectorAll('a').forEach(function(a) {
        a.addEventListener('click', closeNav)
      });
      document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && mn.classList.contains('open')) closeNav();
      });

      // theme toggle — same keys as the panel (localStorage 'theme' + cookie), so
      // the choice carries over between the landing and the control panel.
      function setCookie(v) {
        document.cookie = 'theme=' + v + '; path=/; max-age=31536000; SameSite=Lax';
      }

      function setTheme(m) {
        m = m === 'dark' ? 'dark' : 'light';
        document.documentElement.setAttribute('data-theme', m);
        try {
          localStorage.setItem('theme', m);
        } catch (e) {}
        setCookie(m);
        var meta = document.querySelector('meta[name="theme-color"]');
        if (meta) meta.setAttribute('content', m === 'dark' ? '#0b0d1c' : '#1150ed');
      }
      document.querySelectorAll('[data-theme-toggle]').forEach(function(btn) {
        btn.addEventListener('click', function() {
          var cur = document.documentElement.getAttribute('data-theme') || 'light';
          setTheme(cur === 'dark' ? 'light' : 'dark');
        });
      });

      // reveal on scroll
      var io = new IntersectionObserver(function(es) {
        es.forEach(function(e) {
          if (e.isIntersecting) {
            e.target.classList.add('in');
            io.unobserve(e.target)
          }
        })
      }, {
        threshold: .14
      });
      document.querySelectorAll('.reveal').forEach(function(el, i) {
        el.style.transitionDelay = (i % 3 * 60) + 'ms';
        io.observe(el)
      });
    })();
  </script>
</body>

</html>