<footer class="ficr-footer text-white mt-5" role="contentinfo" aria-label="Informazioni di piè di pagina">
    {{-- Barra social --}}
    <section class="ficr-footer__social border-bottom border-opacity-25">
        <div class="container d-flex flex-column flex-md-row align-items-center justify-content-between py-3 gap-3">
            <span class="small text-white-50 d-none d-md-inline">Seguici sui social</span>
            <div class="d-flex align-items-center gap-3">
                <a href="#" class="ficr-social" aria-label="Facebook" title="Facebook">
                    <i class="fab fa-facebook-f"></i>
                </a>
                <a href="#" class="ficr-social" aria-label="Twitter" title="Twitter">
                    <i class="fab fa-twitter"></i>
                </a>
                <a href="#" class="ficr-social" aria-label="Instagram" title="Instagram">
                    <i class="fab fa-instagram"></i>
                </a>
                <a href="#" class="ficr-social" aria-label="LinkedIn" title="LinkedIn">
                    <i class="fab fa-linkedin"></i>
                </a>
            </div>
        </div>
    </section>

    {{-- Colonne link/contatti --}}
    <section class="py-5">
        <div class="container">
            <div class="row g-4">
                {{-- Info --}}
                <div class="col-12 col-md-6 col-lg-4">
                    <h2 class="h6 fw-bold text-uppercase mb-3 d-flex align-items-center gap-2">
                        <i class="fas fa-stopwatch"></i> Cronometristi Associati
                    </h2>
                    <p class="mb-0 text-white-50">
                        Portale per la gestione gare, disponibilità e cronometristi.
                    </p>
                </div>

                {{-- Collegamenti --}}
                <nav class="col-6 col-md-3 col-lg-2" aria-label="Collegamenti utili">
                    <h3 class="h6 fw-bold text-uppercase mb-3">Collegamenti</h3>
                    <ul class="list-unstyled m-0">
                        <li class="mb-2"><a href="{{ route('login') }}" class="ficr-footer__link">Login</a></li>
                        <li class="mb-2"><a href="{{ route('timekeeper.register') }}"
                                class="ficr-footer__link">Registrati</a></li>
                    </ul>
                </nav>

                {{-- Contatti --}}
                <div class="col-6 col-md-3 col-lg-3">
                    <h3 class="h6 fw-bold text-uppercase mb-3">Contatti</h3>
                    <ul class="list-unstyled m-0 text-white-50">
                        <li class="mb-2"><i class="fas fa-home me-2"></i> Via dello Sport, 100, IT</li>
                        <li class="mb-2"><i class="fas fa-envelope me-2"></i> info@cronometristi.it</li>
                        <li class="mb-2"><i class="fas fa-phone me-2"></i> +39 0123 456789</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    {{-- COOKIE --}}
    <a href="https://www.iubenda.com/privacy-policy/40284629"
        class="iubenda-white iubenda-noiframe iubenda-embed iubenda-noiframe " title="Privacy Policy ">Privacy
        Policy</a>
    <script type="text/javascript">
        (function(w, d) {
            var loader = function() {
                var s = d.createElement("script"),
                    tag = d.getElementsByTagName("script")[0];
                s.src = "https://cdn.iubenda.com/iubenda.js";
                tag.parentNode.insertBefore(s, tag);
            };
            if (w.addEventListener) {
                w.addEventListener("load", loader, false);
            } else if (w.attachEvent) {
                w.attachEvent("onload", loader);
            } else {
                w.onload = loader;
            }
        })(window, document);
    </script>
    <a href="https://www.iubenda.com/privacy-policy/40284629/cookie-policy"
        class="iubenda-white iubenda-noiframe iubenda-embed iubenda-noiframe " title="Cookie Policy ">Cookie
        Policy</a>
    <script type="text/javascript">
        (function(w, d) {
            var loader = function() {
                var s = d.createElement("script"),
                    tag = d.getElementsByTagName("script")[0];
                s.src = "https://cdn.iubenda.com/iubenda.js";
                tag.parentNode.insertBefore(s, tag);
            };
            if (w.addEventListener) {
                w.addEventListener("load", loader, false);
            } else if (w.attachEvent) {
                w.attachEvent("onload", loader);
            } else {
                w.onload = loader;
            }
        })(window, document);
    </script>
    {{-- Copyright --}}
    <div class="ficr-footer__copy small text-white-50">
        <div class="container py-3 text-center">
            © {{ date('Y') }} Cronometristi Associati. Tutti i diritti riservati.
        </div>
    </div>
</footer>
