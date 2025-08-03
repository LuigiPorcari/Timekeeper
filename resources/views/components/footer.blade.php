<footer class="text-center text-lg-start bg-body-tertiary text-muted" role="contentinfo">
    <!-- Social media -->
    <section class="d-flex justify-content-center justify-content-lg-between p-4 border-bottom">
        <div class="me-5 d-none d-lg-block">
            <span>Seguici sui social:</span>
        </div>
        <div>
            <a href="#" class="me-4 text-reset" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
            <a href="#" class="me-4 text-reset" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
            <a href="#" class="me-4 text-reset" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
            <a href="#" class="me-4 text-reset" aria-label="LinkedIn"><i class="fab fa-linkedin"></i></a>
        </div>
    </section>

    <!-- Link utili -->
    <section>
        <div class="container text-center text-md-start mt-5">
            <div class="row mt-3">
                <!-- Informazioni -->
                <div class="col-md-3 col-lg-4 col-xl-3 mx-auto mb-4">
                    <h6 class="text-uppercase fw-bold mb-4">
                        <i class="fas fa-stopwatch me-3"></i>Cronometristi Associati
                    </h6>
                    <p>Portale per la gestione gare, disponibilità e cronometristi.</p>
                </div>

                <!-- Collegamenti -->
                <div class="col-md-3 col-lg-2 col-xl-2 mx-auto mb-4">
                    <h6 class="text-uppercase fw-bold mb-4">Collegamenti</h6>
                    <p><a href="{{ route('login') }}" class="text-reset">Login</a></p>
                    <p><a href="{{ route('timekeeper.register') }}" class="text-reset">Registrati</a></p>
                    <p><a href="#" class="text-reset iubenda-cs-privacy-policy" title="Privacy Policy">Privacy</a>
                    </p>
                    <p><a href="#" class="text-reset iubenda-cs-cookie-policy" title="Cookie Policy">Cookie</a>
                    </p>
                </div>

                <!-- Contatti -->
                <div class="col-md-4 col-lg-3 col-xl-3 mx-auto mb-md-0 mb-4">
                    <h6 class="text-uppercase fw-bold mb-4">Contatti</h6>
                    <p><i class="fas fa-home me-3"></i> Via dello Sport, 100, IT</p>
                    <p><i class="fas fa-envelope me-3"></i> info@cronometristi.it</p>
                    <p><i class="fas fa-phone me-3"></i> +39 0123 456789</p>
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
        class="iubenda-white iubenda-noiframe iubenda-embed iubenda-noiframe " title="Cookie Policy ">Cookie Policy</a>
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
    <!-- Copyright -->
    <div class="text-center p-4 bg-light small">
        © {{ date('Y') }} Cronometristi Associati. Tutti i diritti riservati.
    </div>
</footer>
