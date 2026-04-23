<?php
require_once 'conexao.php';

/*
CREATE TABLE filmes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    genero VARCHAR(50),
    status ENUM('assistido','nao_assistido') DEFAULT 'nao_assistido'
);
*/

function inserirFilme($pdo, $nome, $genero) {
    $stmt = $pdo->prepare("INSERT INTO filmes (nome, genero) VALUES (?, ?)");
    return $stmt->execute([$nome, $genero]);
}
function listarFilmes($pdo, $filtro = null) {
    $sql = "SELECT * FROM filmes";
    if ($filtro == 'nao_assistido') $sql .= " WHERE status = 'nao_assistido'";
    $sql .= " ORDER BY nome";
    return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
}
function buscarFilme($pdo, $id) {
    $stmt = $pdo->prepare("SELECT * FROM filmes WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
function atualizarFilme($pdo, $id, $nome, $genero) {
    $stmt = $pdo->prepare("UPDATE filmes SET nome=?, genero=? WHERE id=?");
    return $stmt->execute([$nome, $genero, $id]);
}
function deletarFilme($pdo, $id) {
    $stmt = $pdo->prepare("DELETE FROM filmes WHERE id=?");
    return $stmt->execute([$id]);
}
function marcarAssistido($pdo, $id) {
    $stmt = $pdo->prepare("UPDATE filmes SET status = 'assistido' WHERE id = ?");
    return $stmt->execute([$id]);
}

$filtro = $_GET['filtro'] ?? null;
$mensagem = '';
$action = $_GET['action'] ?? 'list';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['inserir'])) {
        inserirFilme($pdo, $_POST['nome'], $_POST['genero']);
        $mensagem = "Filme adicionado!";
        $action = 'list';
    } elseif (isset($_POST['editar'])) {
        atualizarFilme($pdo, $_POST['id'], $_POST['nome'], $_POST['genero']);
        $mensagem = "Filme atualizado!";
        $action = 'list';
    }
}
if (isset($_GET['deletar'])) {
    deletarFilme($pdo, $_GET['deletar']);
    $mensagem = "Filme removido!";
    $action = 'list';
}
if (isset($_GET['assistir'])) {
    marcarAssistido($pdo, $_GET['assistir']);
    $mensagem = "Marcado como assistido!";
    $action = 'list';
}
?>
<!DOCTYPE html>
<html>
<head><title>Lista de Filmes</title><style>body{font-family:Arial;margin:20px}</style></head>
<body>
<h1>Filmes para assistir</h1>
<?php if($mensagem): ?><p><strong><?= $mensagem ?></strong></p><?php endif; ?>
<a href="index.html">← Voltar ao menu</a> | 
<a href="?">Todos</a> | 
<a href="?filtro=nao_assistido">Não assistidos</a> |
<?php if($action === 'list'): ?>
    <a href="?action=add">Novo filme</a>
<?php endif; ?>

<?php 
$filmes = listarFilmes($pdo, $filtro);
if($action === 'list'):
    if(count($filmes) > 0): ?>
        <table border="1" cellpadding="8">
            <tr><th>Nome</th><th>Gênero</th><th>Status</th><th>Ações</th></tr>
            <?php foreach($filmes as $f): ?>
            <tr>
                <td><?= htmlspecialchars($f['nome']) ?></td>
                <td><?= htmlspecialchars($f['genero']) ?></td>
                <td><?= ($f['status'] == 'assistido') ? '✅ Assistido' : '⏳ Não assistido' ?></td>
                <td>
                    <?php if($f['status'] == 'nao_assistido'): ?>
                        <a href="?assistir=<?= $f['id'] ?>&filtro=<?= $filtro ?>">Marcar assistido</a> |
                    <?php endif; ?>
                    <a href="?action=edit&id=<?= $f['id'] ?>&filtro=<?= $filtro ?>">Editar</a> |
                    <a href="?deletar=<?= $f['id'] ?>&filtro=<?= $filtro ?>" onclick="return confirm('Remover?')">Excluir</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    <?php else: echo "<p>Nenhum filme encontrado.</p>"; endif;
elseif($action === 'add' || $action === 'edit'):
    $editando = ($action === 'edit');
    $filme = $editando ? buscarFilme($pdo, $_GET['id']) : null;
?>
    <h2><?= $editando ? 'Editar Filme' : 'Novo Filme' ?></h2>
    <form method="POST">
        <?php if($editando): ?><input type="hidden" name="id" value="<?= $filme['id'] ?>"><?php endif; ?>
        Nome: <input type="text" name="nome" value="<?= $editando ? htmlspecialchars($filme['nome']) : '' ?>" required><br>
        Gênero: <input type="text" name="genero" value="<?= $editando ? htmlspecialchars($filme['genero']) : '' ?>" required><br>
        <button type="submit" name="<?= $editando ? 'editar' : 'inserir' ?>">Salvar</button>
        <a href="?filtro=<?= $filtro ?>">Cancelar</a>
    </form>
<?php endif; ?>
</body>
</html>