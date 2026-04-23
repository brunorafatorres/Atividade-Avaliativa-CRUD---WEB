<?php
require_once 'conexao.php';

/*
CREATE TABLE cursos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    carga_horaria INT NOT NULL,
    nivel ENUM('basico','intermediario','avancado') NOT NULL
);
*/

function inserirCurso($pdo, $nome, $carga_horaria, $nivel) {
    $stmt = $pdo->prepare("INSERT INTO cursos (nome, carga_horaria, nivel) VALUES (?, ?, ?)");
    return $stmt->execute([$nome, $carga_horaria, $nivel]);
}
function listarCursos($pdo, $filtro_nivel = null) {
    $sql = "SELECT * FROM cursos";
    if ($filtro_nivel) $sql .= " WHERE nivel = '$filtro_nivel'";
    $sql .= " ORDER BY nome";
    return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
}
function buscarCurso($pdo, $id) {
    $stmt = $pdo->prepare("SELECT * FROM cursos WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
function atualizarCurso($pdo, $id, $nome, $carga_horaria, $nivel) {
    $stmt = $pdo->prepare("UPDATE cursos SET nome=?, carga_horaria=?, nivel=? WHERE id=?");
    return $stmt->execute([$nome, $carga_horaria, $nivel, $id]);
}
function deletarCurso($pdo, $id) {
    $stmt = $pdo->prepare("DELETE FROM cursos WHERE id=?");
    return $stmt->execute([$id]);
}

$filtro = $_GET['nivel'] ?? null;
$mensagem = '';
$action = $_GET['action'] ?? 'list';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['inserir'])) {
        inserirCurso($pdo, $_POST['nome'], $_POST['carga_horaria'], $_POST['nivel']);
        $mensagem = "Curso adicionado!";
        $action = 'list';
    } elseif (isset($_POST['editar'])) {
        atualizarCurso($pdo, $_POST['id'], $_POST['nome'], $_POST['carga_horaria'], $_POST['nivel']);
        $mensagem = "Curso atualizado!";
        $action = 'list';
    }
}
if (isset($_GET['deletar'])) {
    deletarCurso($pdo, $_GET['deletar']);
    $mensagem = "Curso removido!";
    $action = 'list';
}
?>
<!DOCTYPE html>
<html>
<head><title>Cadastro de Cursos</title><style>body{font-family:Arial;margin:20px}</style></head>
<body>
<h1>Cursos Livres</h1>
<?php if($mensagem): ?><p><strong><?= $mensagem ?></strong></p><?php endif; ?>
<a href="index.html">← Voltar ao menu</a> | 
<a href="?">Todos</a> | 
<a href="?nivel=basico">Básico</a> | 
<a href="?nivel=intermediario">Intermediário</a> | 
<a href="?nivel=avancado">Avançado</a> |
<?php if($action === 'list'): ?>
    <a href="?action=add">+ Novo curso</a>
<?php endif; ?>

<?php if($action === 'list'): 
    $cursos = listarCursos($pdo, $filtro);
    if(count($cursos) > 0): ?>
        <table border="1" cellpadding="8">
            <tr><th>Nome</th><th>Carga Horária</th><th>Nível</th><th>Ações</th></tr>
            <?php foreach($cursos as $c): ?>
            <tr>
                <td><?= htmlspecialchars($c['nome']) ?></td>
                <td><?= $c['carga_horaria'] ?>h</td>
                <td>
                    <?php 
                        if($c['nivel']=='basico') echo 'Básico';
                        elseif($c['nivel']=='intermediario') echo 'Intermediário';
                        else echo 'Avançado';
                    ?>
                </td>
                <td>
                    <a href="?action=edit&id=<?= $c['id'] ?>&nivel=<?= $filtro ?>">Editar</a> |
                    <a href="?deletar=<?= $c['id'] ?>&nivel=<?= $filtro ?>" onclick="return confirm('Remover?')">Excluir</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    <?php else: echo "<p>Nenhum curso encontrado.</p>"; endif;
elseif($action === 'add' || $action === 'edit'):
    $editando = ($action === 'edit');
    $curso = $editando ? buscarCurso($pdo, $_GET['id']) : null;
?>
    <h2><?= $editando ? 'Editar Curso' : 'Novo Curso' ?></h2>
    <form method="POST">
        <?php if($editando): ?><input type="hidden" name="id" value="<?= $curso['id'] ?>"><?php endif; ?>
        Nome: <input type="text" name="nome" value="<?= $editando ? htmlspecialchars($curso['nome']) : '' ?>" required><br>
        Carga Horária (horas): <input type="number" name="carga_horaria" value="<?= $editando ? $curso['carga_horaria'] : '' ?>" required><br>
        Nível: 
        <select name="nivel">
            <option value="basico" <?= $editando && $curso['nivel']=='basico' ? 'selected' : '' ?>>Básico</option>
            <option value="intermediario" <?= $editando && $curso['nivel']=='intermediario' ? 'selected' : '' ?>>Intermediário</option>
            <option value="avancado" <?= $editando && $curso['nivel']=='avancado' ? 'selected' : '' ?>>Avançado</option>
        </select><br>
        <button type="submit" name="<?= $editando ? 'editar' : 'inserir' ?>">Salvar</button>
        <a href="?nivel=<?= $filtro ?>">Cancelar</a>
    </form>
<?php endif; ?>
</body>
</html>