<?php
require_once 'conexao.php';

/*
CREATE TABLE leituras (
    id INT AUTO_INCREMENT PRIMARY KEY,
    livro VARCHAR(100) NOT NULL,
    paginas_totais INT NOT NULL,
    paginas_lidas INT DEFAULT 0
);
*/

function inserirLeitura($pdo, $livro, $paginas_totais) {
    $stmt = $pdo->prepare("INSERT INTO leituras (livro, paginas_totais) VALUES (?, ?)");
    return $stmt->execute([$livro, $paginas_totais]);
}
function listarLeituras($pdo) {
    return $pdo->query("SELECT * FROM leituras ORDER BY livro")->fetchAll(PDO::FETCH_ASSOC);
}
function buscarLeitura($pdo, $id) {
    $stmt = $pdo->prepare("SELECT * FROM leituras WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
function atualizarLeitura($pdo, $id, $livro, $paginas_totais) {
    $stmt = $pdo->prepare("UPDATE leituras SET livro=?, paginas_totais=? WHERE id=?");
    return $stmt->execute([$livro, $paginas_totais, $id]);
}
function deletarLeitura($pdo, $id) {
    $stmt = $pdo->prepare("DELETE FROM leituras WHERE id=?");
    return $stmt->execute([$id]);
}
function atualizarProgresso($pdo, $id, $paginas_lidas) {
    $stmt = $pdo->prepare("UPDATE leituras SET paginas_lidas = ? WHERE id = ?");
    return $stmt->execute([$paginas_lidas, $id]);
}
function percentualProgresso($pdo, $id) {
    $leitura = buscarLeitura($pdo, $id);
    if ($leitura && $leitura['paginas_totais'] > 0) {
        return round(($leitura['paginas_lidas'] / $leitura['paginas_totais']) * 100);
    }
    return 0;
}

$mensagem = '';
$action = $_GET['action'] ?? 'list';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['inserir'])) {
        inserirLeitura($pdo, $_POST['livro'], $_POST['paginas_totais']);
        $mensagem = "Livro adicionado!";
        $action = 'list';
    } elseif (isset($_POST['editar'])) {
        atualizarLeitura($pdo, $_POST['id'], $_POST['livro'], $_POST['paginas_totais']);
        $mensagem = "Livro atualizado!";
        $action = 'list';
    } elseif (isset($_POST['atualizar_progresso'])) {
        atualizarProgresso($pdo, $_POST['id'], $_POST['paginas_lidas']);
        $mensagem = "Progresso atualizado!";
        $action = 'list';
    }
}
if (isset($_GET['deletar'])) {
    deletarLeitura($pdo, $_GET['deletar']);
    $mensagem = "Livro removido!";
    $action = 'list';
}
?>
<!DOCTYPE html>
<html>
<head><title>Registro de Leitura</title><style>body{font-family:Arial;margin:20px}</style></head>
<body>
<h1>📖 Registro de Leitura</h1>
<?php if($mensagem): ?><p><strong><?= $mensagem ?></strong></p><?php endif; ?>
<a href="index.html">← Voltar ao menu</a> | 
<?php if($action === 'list'): ?>
    <a href="?action=add">➕ Novo livro</a>
<?php endif; ?>

<?php if($action === 'list'): 
    $leituras = listarLeituras($pdo);
    if(count($leituras) > 0): ?>
        <table border="1" cellpadding="8">
            <tr><th>Livro</th><th>Páginas Totais</th><th>Páginas Lidas</th><th>Progresso</th><th>Ações</th></tr>
            <?php foreach($leituras as $l): 
                $percent = percentualProgresso($pdo, $l['id']);
            ?>
            <tr>
                <td><?= htmlspecialchars($l['livro']) ?></td>
                <td><?= $l['paginas_totais'] ?></td>
                <td><?= $l['paginas_lidas'] ?></td>
                <td>
                    <progress value="<?= $percent ?>" max="100"></progress> <?= $percent ?>%
                </td>
                <td>
                    <form method="POST" style="display:inline-block">
                        <input type="hidden" name="id" value="<?= $l['id'] ?>">
                        <input type="number" name="paginas_lidas" value="<?= $l['paginas_lidas'] ?>" style="width:60px">
                        <button type="submit" name="atualizar_progresso">Atualizar</button>
                    </form>
                    <a href="?action=edit&id=<?= $l['id'] ?>">Editar</a>
                    <a href="?deletar=<?= $l['id'] ?>" onclick="return confirm('Remover?')">Excluir</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    <?php else: echo "<p>Nenhum livro cadastrado.</p>"; endif;
elseif($action === 'add' || $action === 'edit'):
    $editando = ($action === 'edit');
    $leitura = $editando ? buscarLeitura($pdo, $_GET['id']) : null;
?>
    <h2><?= $editando ? 'Editar Livro' : 'Novo Livro' ?></h2>
    <form method="POST">
        <?php if($editando): ?><input type="hidden" name="id" value="<?= $leitura['id'] ?>"><?php endif; ?>
        Título do livro: <input type="text" name="livro" value="<?= $editando ? htmlspecialchars($leitura['livro']) : '' ?>" required><br>
        Páginas totais: <input type="number" name="paginas_totais" value="<?= $editando ? $leitura['paginas_totais'] : '' ?>" required><br>
        <button type="submit" name="<?= $editando ? 'editar' : 'inserir' ?>">Salvar</button>
        <a href="?">Cancelar</a>
    </form>
<?php endif; ?>
</body>
</html>