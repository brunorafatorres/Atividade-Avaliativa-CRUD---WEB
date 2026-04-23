<?php
require_once 'conexao.php';

/*
CREATE TABLE musicas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(100) NOT NULL,
    artista VARCHAR(100) NOT NULL,
    duracao INT NOT NULL
);
*/

function inserirMusica($pdo, $titulo, $artista, $duracao) {
    $stmt = $pdo->prepare("INSERT INTO musicas (titulo, artista, duracao) VALUES (?, ?, ?)");
    return $stmt->execute([$titulo, $artista, $duracao]);
}
function listarMusicas($pdo) {
    return $pdo->query("SELECT * FROM musicas ORDER BY titulo")->fetchAll(PDO::FETCH_ASSOC);
}
function buscarMusica($pdo, $id) {
    $stmt = $pdo->prepare("SELECT * FROM musicas WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
function atualizarMusica($pdo, $id, $titulo, $artista, $duracao) {
    $stmt = $pdo->prepare("UPDATE musicas SET titulo=?, artista=?, duracao=? WHERE id=?");
    return $stmt->execute([$titulo, $artista, $duracao, $id]);
}
function deletarMusica($pdo, $id) {
    $stmt = $pdo->prepare("DELETE FROM musicas WHERE id=?");
    return $stmt->execute([$id]);
}
function tempoTotalPlaylist($pdo) {
    $res = $pdo->query("SELECT SUM(duracao) as total FROM musicas")->fetch();
    return $res['total'] ?? 0;
}
function formatarDuracao($segundos) {
    $horas = floor($segundos / 3600);
    $minutos = floor(($segundos % 3600) / 60);
    $seg = $segundos % 60;
    return ($horas ? $horas . 'h ' : '') . $minutos . 'min ' . $seg . 's';
}

$mensagem = '';
$action = $_GET['action'] ?? 'list';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['inserir'])) {
        inserirMusica($pdo, $_POST['titulo'], $_POST['artista'], $_POST['duracao']);
        $mensagem = "Música adicionada!";
        $action = 'list';
    } elseif (isset($_POST['editar'])) {
        atualizarMusica($pdo, $_POST['id'], $_POST['titulo'], $_POST['artista'], $_POST['duracao']);
        $mensagem = "Música atualizada!";
        $action = 'list';
    }
}
if (isset($_GET['deletar'])) {
    deletarMusica($pdo, $_GET['deletar']);
    $mensagem = "Música removida!";
    $action = 'list';
}
?>
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><title>Playlist de Músicas</title><style>body{font-family:Arial;margin:20px}</style></head>
<body>
<h1>Playlist de Músicas</h1>
<?php if($mensagem): ?><p><strong><?= $mensagem ?></strong></p><?php endif; ?>
<a href="index.html">← Voltar ao menu</a> | 
<?php if($action === 'list'): ?>
    <a href="?action=add">Adicionar música</a>
<?php endif; ?>
<p><strong>Tempo total da playlist: <?= formatarDuracao(tempoTotalPlaylist($pdo)) ?></strong></p>

<?php if($action === 'list'): 
    $musicas = listarMusicas($pdo);
    if(count($musicas) > 0): ?>
        <table border="1" cellpadding="8">
            <tr><th>Título</th><th>Artista</th><th>Duração</th><th>Ações</th></tr>
            <?php foreach($musicas as $m): ?>
            <tr>
                <td><?= htmlspecialchars($m['titulo']) ?></td>
                <td><?= htmlspecialchars($m['artista']) ?></td>
                <td><?= formatarDuracao($m['duracao']) ?></td>
                <td>
                    <a href="?action=edit&id=<?= $m['id'] ?>">Editar</a> |
                    <a href="?deletar=<?= $m['id'] ?>" onclick="return confirm('Remover?')">Excluir</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    <?php else: echo "<p>Nenhuma música cadastrada.</p>"; endif;
elseif($action === 'add' || $action === 'edit'):
    $editando = ($action === 'edit');
    $musica = $editando ? buscarMusica($pdo, $_GET['id']) : null;
?>
    <h2><?= $editando ? 'Editar Música' : 'Nova Música' ?></h2>
    <form method="POST">
        <?php if($editando): ?><input type="hidden" name="id" value="<?= $musica['id'] ?>"><?php endif; ?>
        Título: <input type="text" name="titulo" value="<?= $editando ? htmlspecialchars($musica['titulo']) : '' ?>" required><br>
        Artista: <input type="text" name="artista" value="<?= $editando ? htmlspecialchars($musica['artista']) : '' ?>" required><br>
        Duração (segundos): <input type="number" name="duracao" value="<?= $editando ? $musica['duracao'] : '' ?>" required><br>
        <button type="submit" name="<?= $editando ? 'editar' : 'inserir' ?>">Salvar</button>
        <a href="?">Cancelar</a>
    </form>
<?php endif; ?>
</body>
</html>