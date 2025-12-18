<?php
// Pages/create_post.php
include_once('../Inc/Components/header.php');
include_once('../Inc/Components/nav.php');

// Simple authorization check
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'journaliste') {
    add_notification('Accès refusé. Vous devez être journaliste pour publier un article.', 'error');
    header('Location: ../index.php');
    exit;
}

require_once '../Inc/Constants/db.php';
$conn = connect_db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        add_notification('Session invalide (CSRF).', 'error');
    } else {
        $title = htmlspecialchars(trim($_POST['title'] ?? ''));
        $content = htmlspecialchars(trim($_POST['content'] ?? ''));
        $user_id = $_SESSION['user_id'];

        if (!empty($title) && !empty($content)) {
            try {
                $stmt = $conn->prepare("INSERT INTO posts (title, content, author_id, created_at) VALUES (?, ?, ?, datetime('now'))");
                $stmt->execute([$title, $content, $user_id]);
                add_notification('Article publié avec succès !', 'success');
                header('Location: ../index.php');
                exit;
            } catch (PDOException $e) {
                add_notification('Erreur lors de la publication : ' . $e->getMessage(), 'error');
            }
        } else {
            add_notification('Veuillez remplir tous les champs.', 'warning');
        }
    }
}
?>

<div class="min-h-screen pt-24 pb-12 px-4 sm:px-6 lg:px-8 bg-gray-50">
    <div class="max-w-3xl mx-auto">
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-8 py-6">
                <h1 class="text-2xl font-bold text-white flex items-center">
                    <i class="fas fa-pen-nib mr-3"></i>
                    Publier un nouvel article
                </h1>
                <p class="text-blue-100 mt-2">Partagez des informations avec la communauté E Conscience</p>
            </div>

            <form method="POST" action="create_post.php" class="p-8 space-y-6">
                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">

                <div class="space-y-2">
                    <label for="title" class="text-sm font-semibold text-gray-700">Titre de l'article</label>
                    <input type="text" name="title" id="title" required
                        class="w-full px-4 py-3 rounded-xl border-2 border-gray-100 focus:border-blue-600 focus:ring-0 transition-all"
                        placeholder="Entrez un titre accrocheur...">
                </div>

                <div class="space-y-2">
                    <label for="content" class="text-sm font-semibold text-gray-700">Contenu</label>
                    <textarea name="content" id="content" rows="10" required
                        class="w-full px-4 py-3 rounded-xl border-2 border-gray-100 focus:border-blue-600 focus:ring-0 transition-all"
                        placeholder="Rédigez votre article ici..."></textarea>
                </div>

                <div class="flex items-center justify-end space-x-4 pt-4">
                    <a href="../index.php"
                        class="px-6 py-3 text-gray-600 font-semibold hover:text-gray-900 transition-colors">
                        Annuler
                    </a>
                    <button type="submit"
                        class="px-8 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all">
                        Publier l'article
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include_once('../Inc/Components/main_footer.php'); ?>