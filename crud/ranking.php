<?php
require_once 'conexao.php';

/*
CREATE TABLE rankings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    pontuacao INT NOT NULL
);
*/

function inserirPontuacao($pdo, $nome, $pontuacao) {
    $stmt = $pdo->prepare("INSERT INTO rankings (nome, pontuacao) VALUES (?, ?)");
    return $stmt->execute([$nome, $pontuacao]);
}
function listarTop5($pdo) {
    return $pdo->query("SELECT * FROM rankings ORDER BY pontuacao DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
}
function listarTodos($pdo) {
    return $pdo->query("SELECT * FROM rankings ORDER BY pontuacao DESC")->fetchAll(PDO::FETCH_ASSOC);
}
function buscarPontuacao($pdo, $id) {
    $stmt = $pdo->prepare("SELECT * FROM rankings WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
function atualizarPontuacao($pdo, $id, $nome, $pontuacao) {
    $stmt = $pdo->prepare("UPDATE rankings SET nome=?, pontuacao=? WHERE id=?");
    return $stmt->execute([$nome, $pontuacao, $id]);
}
function deletarPontuacao($pdo, $id) {
    $stmt = $pdo->prepare("DELETE FROM rankings WHERE id=?");
    return $stmt->execute([$id]);
}

$mensagem = '';
$action = $_GET['action'] ?? 'list';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['inserir'])) {
        inserirPontuacao($pdo, $_POST['nome'], $_POST['pontuacao']);
        $mensagem = "Participante adicionado!";
        $action = 'list';
    } elseif (isset($_POST['editar'])) {
        atualizarPontuacao($pdo, $_POST['id'], $_POST['nome'], $_POST['pontuacao']);
        $mensagem = "Pontuação atualizada!";
        $action = 'list';
    }
}
if (isset($_GET['deletar'])) {
    deletarPontuacao($pdo, $_GET['deletar']);
    $mensagem = "Participante removido!";
    $action = 'list';
}
?>
<!DOCTYPE html>
<html>
<head><title>Ranking de Pontuação</title><style>body{font-family:Arial;margin:20px}</style></head>
<body>
<h1>Ranking de Pontuação (Gamificado)</h1>
<?php if($mensagem): ?><p><strong><?= $mensagem ?></strong></p><?php endif; ?>
<a href="index.html">← Voltar ao menu</a> | 
<?php if($action === 'list'): ?>
    <a href="?action=add">Novo participante</a> |
    <a href="?action=all">Ver todos</a>
<?php endif; ?>

<?php 
$exibirTodos = ($action === 'all');
if($action === 'list' || $exibirTodos):
    if($exibirTodos) {
        $participantes = listarTodos($pdo);
        echo "<h2>Todos os participantes</h2>";
    } else {
        $participantes = listarTop5($pdo);
        echo "<h2>TOP 5</h2>";
    }
    if(count($participantes) > 0): ?>
        <table border="1" cellpadding="8">
            <tr><th>Posição</th><th>Nome</th><th>Pontuação</th><th>Ações</th></tr>
            <?php 
            $pos = 1;
            foreach($participantes as $p): ?>
            <tr>
                <td><?= $pos++ ?>º</td>
                <td><?= htmlspecialchars($p['nome']) ?></td>
                <td><?= number_format($p['pontuacao']) ?> pts</td>
                <td>
                    <a href="?action=edit&id=<?= $p['id'] ?>">Editar</a> |
                    <a href="?deletar=<?= $p['id'] ?>" onclick="return confirm('Remover?')">Excluir</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    <?php else: echo "<p>Nenhum participante cadastrado.</p>"; endif;
    if($exibirTodos) echo '<a href="?">← Voltar ao TOP 5</a>';
elseif($action === 'add' || $action === 'edit'):
    $editando = ($action === 'edit');
    $ponto = $editando ? buscarPontuacao($pdo, $_GET['id']) : null;
?>
    <h2><?= $editando ? 'Editar Participante' : 'Novo Participante' ?></h2>
    <form method="POST">
        <?php if($editando): ?><input type="hidden" name="id" value="<?= $ponto['id'] ?>"><?php endif; ?>
        Nome: <input type="text" name="nome" value="<?= $editando ? htmlspecialchars($ponto['nome']) : '' ?>" required><br>
        Pontuação: <input type="number" name="pontuacao" value="<?= $editando ? $ponto['pontuacao'] : '' ?>" required><br>
        <button type="submit" name="<?= $editando ? 'editar' : 'inserir' ?>">Salvar</button>
        <a href="?">Cancelar</a>
    </form>
<?php endif; ?>
</body>
</html>