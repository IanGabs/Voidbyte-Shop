<footer>
        <div class="footer-content">
            <div class="footer-brand">
                <div class="logo-text">VOIDBYTE</div>
                <p>O vácuo perfeito para o seu setup. Especialistas em hardware de alto desempenho e estética minimalista dark.</p>
                <div class="social-icons">
                    <a href="#"><i class="fab fa-github"></i></a>
                    <a href="#"><i class="fab fa-discord"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                </div>
            </div>
            
            <div class="footer-col">
                <h4>Data Links</h4>
                <ul>
                    <li><a href="produtos.php">Catálogo de Hardware</a></li>
                    <li><a href="#">Lançamentos</a></li>
                    <li><a href="#">Montagem de Setup</a></li>
                </ul>
            </div>
            
            <div class="footer-col">
                <h4>Protocolos</h4>
                <ul>
                    <li><a href="#">Termos de Serviço</a></li>
                    <li><a href="#">Política de Privacidade</a></li>
                    <li><a href="#">Garantia e Suporte</a></li>
                </ul>
            </div>
        </div>
        
        <div class="copyright">
            © <?php echo date('Y'); ?> Voidbyte Shop. Conexão criptografada.
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const profileTrigger = document.getElementById('profileTrigger');
            const profileDropdown = document.getElementById('profileDropdown');

            if (profileTrigger && profileDropdown) {
                profileTrigger.addEventListener('click', (e) => {
                    e.stopPropagation();
                    profileDropdown.classList.toggle('show');
                });

                document.addEventListener('click', (e) => {
                    if (!profileDropdown.contains(e.target) && !profileTrigger.contains(e.target)) {
                        profileDropdown.classList.remove('show');
                    }
                });
            }
        });
    </script>
</body>
</html>