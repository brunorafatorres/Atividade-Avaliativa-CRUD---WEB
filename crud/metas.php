<?php
require_once 'conexao.php';

/*
CREATE TABLE metas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    meta VARCHAR(200) NOT NULL,
    prazo DATE NOT NULL,
    status ENUM('pendente','concluida') DEFAULT 'pendente',
    progresso INT DEFAULT 0 CHECK (progresso BETWEEN 0 AND 100)
);
*/

function inserirMeta($pdo, $meta, $prazo) {
    $stmt = $pdo->prepare("INSERT INTO metas (meta, prazo) VALUES (?, ?)");
    return $stmt->execute([$meta, $prazo]);
}
function listarMetas($pdo) {
    return $pdo->query("SELECT * FROM metas ORDER BY prazo ASC")->fetchAll(PDO::FETCH_ASSOC);
}
function buscarMeta($pdo, $id) {
    $stmt = $pdo->prepare("SELECT * FROM metas WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
function atualizarMeta($pdo, $id, $meta, $prazo) {
    $stmt = $pdo->prepare("UPDATE metas SET meta=?, prazo=? WHERE id=?");
    return $stmt->execute([$meta, $prazo, $id]);
}
function deletarMeta($pdo, $id) {
    $stmt = $pdo->prepare("DELETE FROM metas WHERE id=?");
    return $stmt->execute([$id]);
}
function atualizarProgressoMeta($pdo, $id, $progresso) {
    $stmt = $pdo->prepare("UPDATE metas SET progresso = ?, status = IF(? >= 100, 'concluida', 'pendente') WHERE id = ?");
    return $stmt->execute([$progresso, $progresso, $id]);
}
function estaAtrasada($prazo, $progresso) {
    return ($progresso < 100 && strtotime($prazo) < time());
}

$mensagem = '';
$action = $_GET['action'] ?? 'list';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['inserir'])) {
        inserirMeta($pdo, $_POST['meta'], $_POST['prazo']);
        $mensagem = "Meta adicionada!";
        $action = 'list';
    } elseif (isset($_POST['editar'])) {
        atualizarMeta($pdo, $_POST['id'], $_POST['meta'], $_POST['prazo']);
        $mensagem = "Meta atualizada!";
        $action = 'list';
    } elseif (isset($_POST['atualizar_progresso'])) {
        atualizarProgressoMeta($pdo, $_POST['id'], $_POST['progresso']);
        $mensagem = "Progresso atualizado!";
        $action = 'list';
    }
}
if (isset($_GET['deletar'])) {
    deletarMeta($pdo, $_GET['deletar']);
    $mensagem = "Meta removida!";
    $action = 'list';
}
?>
<!DOCTYPE html>
<html>
<head><title>Metas Pessoais</title><style>body{font-family:Arial;margin:20px}</style></head>
<body>
<h1>🎯 Sistema de Metas Pessoais</h1>
<?php if($mensagem): ?><p><strong><?= $mensagem ?></strong></p><?php endif; ?>
<a href="index.html">← Voltar ao menu</a> | 
<?php if($action === 'list'): ?>
    <a href="?action=add">➕ Nova meta</a>
<?php endif; ?>

<?php if($action === 'list'): 
    $metas = listarMetas($pdo);
    $pendentes = array_filter($metas, fn($m) => $m['status'] == 'pendente');
    $concluidas = array_filter($metas, fn($m) => $m['status'] == 'concluida');
?>
    <h2>📌 Metas Pendentes</h2>
    <?php if(count($pendentes) > 0): ?>
        <table border="1" cellpadding="8">
            <tr><th>Meta</th><th>Prazo</th><th>Progresso</th><th>Status</th><th>Ações</th></tr>
            <?php foreach($pendentes as $m): 
                $atrasada = estaAtrasada($m['prazo'], $m['progresso']);
            ?>
            <tr>
                <td><?= htmlspecialchars($m['meta']) ?></td>
                <td><?= date('d/m/Y', strtotime($m['prazo'])) ?> <?= $atrasada ? '<span style="color:red">(Atrasada!)</span>' : '' ?></td>
                <td>
                    <form method="POST" style="display:inline-block">
                        <input type="hidden" name="id" value="<?= $m['id'] ?>">
                        <input type="number" name="progresso" value="<?= $m['progresso'] ?>" min="0" max="100" style="width:60px"> %
                        <button type="submit" name="atualizar_progresso">Atualizar</button>
                    </form>
                </td>
                <td><?= $m['progresso'] ?>%</td>
                <td>
                    <a href="?action=edit&id=<?= $m['id'] ?>">Editar</a> |
                    <a href="?deletar=<?= $m['id'] ?>" onclick="return confirm('Remover?')">Excluir</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    <?php else: echo "<p>Nenhuma meta pendente.</p>"; endif; ?>

    <h2>✅ Metas Concluídas</h2>
    <?php if(count($concluidas) > 0): ?>
        <table border="1" cellpadding="8">
            <tr><th>Meta</th><th>Prazo</th><th>Progresso</th><th>Ações</th></tr>
            <?php foreach($concluidas as $m): ?>
            <tr>
                <td><?= htmlspecialchars($m['meta']) ?></td>
                <td><?= date('d/m/Y', strtotime($m['prazo'])) ?></td>
                <td>100%</td>
                <td>
                    <a href="?deletar=<?= $m['id'] ?>" onclick="return confirm('Remover?')">Excluir</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    <?php else: echo "<p>Nenhuma meta concluída.</p>"; endif;
elseif($action === 'add' || $action === 'edit'):
    $editando = ($action === 'edit');
    $meta = $editando ? buscarMeta($pdo, $_GET['id']) : null;
?>
    <h2><?= $editando ? 'Editar Meta' : 'Nova Meta' ?></h2>
    <form method="POST">
        <?php if($editando): ?><input type="hidden" name="id" value="<?= $meta['id'] ?>"><?php endif; ?>
        Meta: <input type="text" name="meta" size="50" value="<?= $editando ? htmlspecialchars($meta['meta']) : '' ?>" required><br>
        Prazo: <input type="date" name="prazo" value="<?= $editando ? $meta['prazo'] : '' ?>" required><br>
        <button type="submit" name="<?= $editando ? 'editar' : 'inserir' ?>">Salvar</button>
        <a href="?">Cancelar</a>
    </form>
<?php endif; ?>
</body>
</html>