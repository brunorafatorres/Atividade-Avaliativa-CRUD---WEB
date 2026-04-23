<?php
require_once 'conexao.php';

/*
CREATE TABLE ideias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(100) NOT NULL,
    descricao TEXT,
    dificuldade ENUM('baixa','media','alta') NOT NULL
);
*/

function inserirIdeia($pdo, $titulo, $descricao, $dificuldade) {
    $stmt = $pdo->prepare("INSERT INTO ideias (titulo, descricao, dificuldade) VALUES (?, ?, ?)");
    return $stmt->execute([$titulo, $descricao, $dificuldade]);
}
function listarIdeias($pdo, $filtro = null) {
    $sql = "SELECT * FROM ideias";
    if ($filtro) $sql .= " WHERE dificuldade = '$filtro'";
    $sql .= " ORDER BY titulo";
    return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
}
function buscarIdeia($pdo, $id) {
    $stmt = $pdo->prepare("SELECT * FROM ideias WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
function atualizarIdeia($pdo, $id, $titulo, $descricao, $dificuldade) {
    $stmt = $pdo->prepare("UPDATE ideias SET titulo=?, descricao=?, dificuldade=? WHERE id=?");
    return $stmt->execute([$titulo, $descricao, $dificuldade, $id]);
}
function deletarIdeia($pdo, $id) {
    $stmt = $pdo->prepare("DELETE FROM ideias WHERE id=?");
    return $stmt->execute([$id]);
}

$filtro = $_GET['dificuldade'] ?? null;
$mensagem = '';
$action = $_GET['action'] ?? 'list';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['inserir'])) {
        inserirIdeia($pdo, $_POST['titulo'], $_POST['descricao'], $_POST['dificuldade']);
        $mensagem = "Ideia adicionada!";
        $action = 'list';
    } elseif (isset($_POST['editar'])) {
        atualizarIdeia($pdo, $_POST['id'], $_POST['titulo'], $_POST['descricao'], $_POST['dificuldade']);
        $mensagem = "Ideia atualizada!";
        $action = 'list';
    }
}
if (isset($_GET['deletar'])) {
    deletarIdeia($pdo, $_GET['deletar']);
    $mensagem = "Ideia removida!";
    $action = 'list';
}
?>
<!DOCTYPE html>
<html>
<head><title>Banco de Ideias</title><style>body{font-family:Arial;margin:20px}</style></head>
<body>
<h1>💡 Banco de Ideias de Projetos</h1>
<?php if($mensagem): ?><p><strong><?= $mensagem ?></strong></p><?php endif; ?>
<a href="index.html">← Voltar ao menu</a> | 
<a href="?">Todas</a> | 
<a href="?dificuldade=baixa">Baixa dificuldade</a> | 
<a href="?dificuldade=media">Média dificuldade</a> | 
<a href="?dificuldade=alta">Alta dificuldade</a> |
<?php if($action === 'list'): ?>
    <a href="?action=add">➕ Nova ideia</a>
<?php endif; ?>

<?php if($action === 'list'): 
    $ideias = listarIdeias($pdo, $filtro);
    if(count($ideias) > 0): ?>
        <table border="1" cellpadding="8">
            <tr><th>Título</th><th>Descrição</th><th>Dificuldade</th><th>Ações</th></tr>
            <?php foreach($ideias as $i): ?>
            <tr>
                <td><?= htmlspecialchars($i['titulo']) ?></td>
                <td><?= nl2br(htmlspecialchars($i['descricao'])) ?></td>
                <td>
                    <?php 
                        if($i['dificuldade']=='baixa') echo '🟢 Baixa';
                        elseif($i['dificuldade']=='media') echo '🟡 Média';
                        else echo '🔴 Alta';
                    ?>
                </td>
                <td>
                    <a href="?action=edit&id=<?= $i['id'] ?>&dificuldade=<?= $filtro ?>">Editar</a> |
                    <a href="?deletar=<?= $i['id'] ?>&dificuldade=<?= $filtro ?>" onclick="return confirm('Remover?')">Excluir</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    <?php else: echo "<p>Nenhuma ideia encontrada.</p>"; endif;
elseif($action === 'add' || $action === 'edit'):
    $editando = ($action === 'edit');
    $ideia = $editando ? buscarIdeia($pdo, $_GET['id']) : null;
?>
    <h2><?= $editando ? 'Editar Ideia' : 'Nova Ideia' ?></h2>
    <form method="POST">
        <?php if($editando): ?><input type="hidden" name="id" value="<?= $ideia['id'] ?>"><?php endif; ?>
        Título: <input type="text" name="titulo" value="<?= $editando ? htmlspecialchars($ideia['titulo']) : '' ?>" required><br>
        Descrição: <textarea name="descricao" required><?= $editando ? htmlspecialchars($ideia['descricao']) : '' ?></textarea><br>
        Dificuldade:
        <select name="dificuldade">
            <option value="baixa" <?= $editando && $ideia['dificuldade']=='baixa' ? 'selected' : '' ?>>Baixa</option>
            <option value="media" <?= $editando && $ideia['dificuldade']=='media' ? 'selected' : '' ?>>Média</option>
            <option value="alta" <?= $editando && $ideia['dificuldade']=='alta' ? 'selected' : '' ?>>Alta</option>
        </select><br>
        <button type="submit" name="<?= $editando ? 'editar' : 'inserir' ?>">Salvar</button>
        <a href="?dificuldade=<?= $filtro ?>">Cancelar</a>
    </form>
<?php endif; ?>
</body>
</html>