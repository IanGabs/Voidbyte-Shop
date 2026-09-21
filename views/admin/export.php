<?php include __DIR__ . '/../layout/header.php'; ?>

<main class="container" style="padding: 4rem 2rem; min-height: 80vh;">
    <div class="form-header">
        <h1><i class="fas fa-random"></i> Adapter: XML → JSON</h1>
        <p>O sistema exporta em XML, o Adapter converte para JSON sem que o cliente saiba.</p>
    </div>

    <div style="display: flex; gap: 14px; flex-wrap: wrap; margin-bottom: 2.5rem;">
        <a href="export.php?formato=xml" class="btn-secondary"><i class="fas fa-file-code"></i> Baixar XML</a>
        <a href="export.php?formato=json" class="btn-primary"><i class="fas fa-file-download"></i> Baixar JSON</a>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
        <div>
            <h3 style="color: var(--orange); margin-bottom: 1rem;">
                <i class="fas fa-database"></i> Adaptado — saída XML
            </h3>
            <pre style="background: var(--surface-2); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 1.2rem; overflow-x: auto; font-size: .82rem; color: var(--text-muted); max-height: 520px;"><?php echo htmlspecialchars($xml); ?></pre>
        </div>

        <div>
            <h3 style="color: var(--cyan); margin-bottom: 1rem;">
                <i class="fas fa-plug"></i> Alvo — saída JSON (via Adapter)
            </h3>
            <pre style="background: var(--surface-2); border: 1px solid var(--border-hover); border-radius: var(--radius-lg); padding: 1.2rem; overflow-x: auto; font-size: .82rem; color: var(--text-muted); max-height: 520px;"><?php echo htmlspecialchars($json); ?></pre>
        </div>
    </div>
</main>

<?php include __DIR__ . '/../layout/footer.php'; ?>
