<?php
require_once '../Inc/Components/header.php';
require_once '../Inc/Components/nav.php';

$pdo = connect_db();

// Récupération des statistiques de base
$stats = [];

// Total des signalements
$stmt = $pdo->query("SELECT COUNT(*) as total FROM signalements");
$stats['total_signalements'] = $stmt->fetch()['total'];

// Signalements ce mois
$stmt = $pdo->query("SELECT COUNT(*) as total FROM signalements WHERE strftime('%Y-%m', date_signalement) = strftime('%Y-%m', 'now')");
$stats['signalements_mois'] = $stmt->fetch()['total'];

// Signalements cette semaine
$stmt = $pdo->query("SELECT COUNT(*) as total FROM signalements WHERE date_signalement >= date('now', '-7 days')");
$stats['signalements_semaine'] = $stmt->fetch()['total'];

// Signalements aujourd'hui
$stmt = $pdo->query("SELECT COUNT(*) as total FROM signalements WHERE date(date_signalement) = date('now')");
$stats['signalements_aujourd_hui'] = $stmt->fetch()['total'];

// Utilisateurs actifs
$stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role != 'guest'");
$stats['utilisateurs_actifs'] = $stmt->fetch()['total'];

// Signalements par statut
$stmt = $pdo->query("SELECT statut, COUNT(*) as count FROM signalements GROUP BY statut");
$statuts = $stmt->fetchAll();

// Signalements par type d'incident
$stmt = $pdo->query("SELECT type_incident, COUNT(*) as count FROM signalements GROUP BY type_incident ORDER BY count DESC LIMIT 5");
$types_incidents = $stmt->fetchAll();

// Signalements par priorité
$stmt = $pdo->query("SELECT priorite, COUNT(*) as count FROM signalements GROUP BY priorite");
$priorites = $stmt->fetchAll();

// Top 5 des localisations
$stmt = $pdo->query("SELECT localisation, COUNT(*) as count FROM signalements WHERE localisation IS NOT NULL AND localisation != '' GROUP BY localisation ORDER BY count DESC LIMIT 5");
$localisations = $stmt->fetchAll();

// Utilisateurs les plus actifs (pour admin/modérateur)
$top_users = [];
if ($user && in_array($user['role'], ['admin', 'moderator'])) {
    $stmt = $pdo->query("SELECT u.nom, u.prenom, COUNT(s.id) as count FROM users u LEFT JOIN signalements s ON u.id = s.user_id GROUP BY u.id ORDER BY count DESC LIMIT 5");
    $top_users = $stmt->fetchAll();
}
?>

<style>
    /* Styles personnalisés pour une interface moderne et douce */
    :root {
        --primary-soft: #4f46e5;
        --secondary-soft: #06b6d4;
        --accent-soft: #10b981;
        --warning-soft: #f59e0b;
        --danger-soft: #ef4444;
        --neutral-50: #f8fafc;
        --neutral-100: #f1f5f9;
        --neutral-200: #e2e8f0;
        --neutral-300: #cbd5e1;
        --neutral-600: #475569;
        --neutral-700: #334155;
        --neutral-800: #1e293b;
        --neutral-900: #0f172a;
    }

    .stats-container {
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        min-height: 100vh;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    }

    .stats-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 16px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .stats-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }

    .metric-card {
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.9) 0%, rgba(248, 250, 252, 0.9) 100%);
        border: 1px solid rgba(226, 232, 240, 0.5);
        border-radius: 12px;
        padding: 1.5rem;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .metric-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: var(--gradient);
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .metric-card:hover::before {
        opacity: 1;
    }

    .metric-card:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        border-color: rgba(79, 70, 229, 0.2);
    }

    .metric-number {
        font-size: 2.5rem;
        font-weight: 700;
        line-height: 1;
        margin-bottom: 0.5rem;
    }

    .metric-label {
        color: var(--neutral-600);
        font-size: 0.875rem;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .section-title {
        color: var(--neutral-800);
        font-size: 1.5rem;
        font-weight: 600;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .section-title i {
        padding: 0.5rem;
        border-radius: 8px;
        font-size: 1rem;
    }

    .list-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1rem;
        border-radius: 8px;
        background: rgba(248, 250, 252, 0.5);
        border: 1px solid rgba(226, 232, 240, 0.5);
        margin-bottom: 0.75rem;
        transition: all 0.2s ease;
    }

    .list-item:hover {
        background: rgba(255, 255, 255, 0.8);
        border-color: rgba(79, 70, 229, 0.2);
        transform: translateX(4px);
    }

    .status-indicator {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        margin-right: 0.75rem;
    }

    .badge {
        padding: 0.375rem 0.75rem;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 600;
        text-align: center;
        min-width: 2.5rem;
    }

    .fade-in {
        animation: fadeIn 0.6s ease-out;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .btn-modern {
        background: linear-gradient(135deg, var(--primary-soft) 0%, var(--secondary-soft) 100%);
        color: white;
        padding: 0.75rem 2rem;
        border-radius: 8px;
        font-weight: 500;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.3s ease;
        border: none;
        box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.3);
    }

    .btn-modern:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 15px -3px rgba(79, 70, 229, 0.4);
        color: white;
        text-decoration: none;
    }
</style>

<body class="stats-container">
    <div class="container mx-auto px-4 py-8 max-w-7xl">
        <!-- En-tête moderne -->
        <div class="text-center mb-12 fade-in">
            <h1 class="text-4xl font-bold text-neutral-800 mb-4">
                <i class="fas fa-chart-line text-primary-soft mr-3"></i>
                Tableau de Bord Statistiques
            </h1>
            <p class="text-lg text-neutral-600 max-w-2xl mx-auto">
                Vue d'ensemble des données de la plateforme E Conscience
            </p>
        </div>

        <!-- Métriques principales -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-12">
            <!-- Total Signalements -->
            <div class="metric-card fade-in"
                style="--gradient: linear-gradient(90deg, var(--primary-soft), var(--secondary-soft)); animation-delay: 0.1s;">
                <div class="flex items-center justify-between mb-3">
                    <i class="fas fa-exclamation-triangle text-2xl" style="color: var(--primary-soft);"></i>
                </div>
                <div class="metric-number" style="color: var(--primary-soft);">
                    <?= number_format($stats['total_signalements']) ?>
                </div>
                <div class="metric-label">Total Signalements</div>
            </div>

            <!-- Ce Mois -->
            <div class="metric-card fade-in"
                style="--gradient: linear-gradient(90deg, var(--accent-soft), var(--secondary-soft)); animation-delay: 0.2s;">
                <div class="flex items-center justify-between mb-3">
                    <i class="fas fa-calendar-alt text-2xl" style="color: var(--accent-soft);"></i>
                </div>
                <div class="metric-number" style="color: var(--accent-soft);">
                    <?= number_format($stats['signalements_mois']) ?>
                </div>
                <div class="metric-label">Ce Mois</div>
            </div>

            <!-- Cette Semaine -->
            <div class="metric-card fade-in"
                style="--gradient: linear-gradient(90deg, var(--warning-soft), var(--accent-soft)); animation-delay: 0.3s;">
                <div class="flex items-center justify-between mb-3">
                    <i class="fas fa-calendar-week text-2xl" style="color: var(--warning-soft);"></i>
                </div>
                <div class="metric-number" style="color: var(--warning-soft);">
                    <?= number_format($stats['signalements_semaine']) ?>
                </div>
                <div class="metric-label">Cette Semaine</div>
            </div>

            <!-- Aujourd'hui -->
            <div class="metric-card fade-in"
                style="--gradient: linear-gradient(90deg, var(--danger-soft), var(--warning-soft)); animation-delay: 0.4s;">
                <div class="flex items-center justify-between mb-3">
                    <i class="fas fa-clock text-2xl" style="color: var(--danger-soft);"></i>
                </div>
                <div class="metric-number" style="color: var(--danger-soft);">
                    <?= number_format($stats['signalements_aujourd_hui']) ?>
                </div>
                <div class="metric-label">Aujourd'hui</div>
            </div>

            <!-- Utilisateurs Actifs -->
            <div class="metric-card fade-in"
                style="--gradient: linear-gradient(90deg, var(--secondary-soft), var(--primary-soft)); animation-delay: 0.5s;">
                <div class="flex items-center justify-between mb-3">
                    <i class="fas fa-users text-2xl" style="color: var(--secondary-soft);"></i>
                </div>
                <div class="metric-number" style="color: var(--secondary-soft);">
                    <?= number_format($stats['utilisateurs_actifs']) ?>
                </div>
                <div class="metric-label">Utilisateurs Actifs</div>
            </div>
        </div>

        <!-- Grille des détails -->
        <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-8">
            <!-- Par Statut -->
            <div class="stats-card p-6 fade-in" style="animation-delay: 0.6s;">
                <h3 class="section-title">
                    <i class="fas fa-tasks"
                        style="background: linear-gradient(135deg, var(--primary-soft), var(--secondary-soft)); color: white;"></i>
                    Répartition par Statut
                </h3>
                <div class="space-y-3">
                    <?php foreach ($statuts as $statut): ?>
                        <div class="list-item">
                            <div class="flex items-center">
                                <?php
                                $statusColor = '';
                                switch (strtolower($statut['statut'])) {
                                    case 'en attente':
                                        $statusColor = 'var(--warning-soft)';
                                        break;
                                    case 'en cours':
                                        $statusColor = 'var(--secondary-soft)';
                                        break;
                                    case 'résolu':
                                        $statusColor = 'var(--accent-soft)';
                                        break;
                                    case 'fermé':
                                        $statusColor = 'var(--neutral-600)';
                                        break;
                                    default:
                                        $statusColor = 'var(--primary-soft)';
                                }
                                ?>
                                <div class="status-indicator" style="background-color: <?= $statusColor ?>;"></div>
                                <span class="text-neutral-700 font-medium"><?= htmlspecialchars($statut['statut']) ?></span>
                            </div>
                            <span class="badge"
                                style="background-color: rgba(79, 70, 229, 0.1); color: var(--primary-soft);">
                                <?= number_format($statut['count']) ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Top Types d'Incidents -->
            <div class="stats-card p-6 fade-in" style="animation-delay: 0.7s;">
                <h3 class="section-title">
                    <i class="fas fa-exclamation-circle"
                        style="background: linear-gradient(135deg, var(--warning-soft), var(--danger-soft)); color: white;"></i>
                    Types d'Incidents
                </h3>
                <div class="space-y-3">
                    <?php foreach ($types_incidents as $index => $type): ?>
                        <div class="list-item">
                            <div class="flex items-center">
                                <div class="w-6 h-6 rounded-full flex items-center justify-center text-white text-xs font-bold mr-3"
                                    style="background: linear-gradient(135deg, var(--warning-soft), var(--danger-soft));">
                                    <?= $index + 1 ?>
                                </div>
                                <span
                                    class="text-neutral-700 font-medium"><?= htmlspecialchars($type['type_incident']) ?></span>
                            </div>
                            <span class="badge"
                                style="background-color: rgba(245, 158, 11, 0.1); color: var(--warning-soft);">
                                <?= number_format($type['count']) ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Par Priorité -->
            <div class="stats-card p-6 fade-in" style="animation-delay: 0.8s;">
                <h3 class="section-title">
                    <i class="fas fa-flag"
                        style="background: linear-gradient(135deg, var(--danger-soft), var(--warning-soft)); color: white;"></i>
                    Niveaux de Priorité
                </h3>
                <div class="space-y-3">
                    <?php foreach ($priorites as $priorite): ?>
                        <div class="list-item">
                            <div class="flex items-center">
                                <?php
                                $priorityColor = '';
                                switch (strtolower($priorite['priorite'])) {
                                    case 'faible':
                                        $priorityColor = 'var(--accent-soft)';
                                        break;
                                    case 'moyenne':
                                        $priorityColor = 'var(--warning-soft)';
                                        break;
                                    case 'élevée':
                                    case 'haute':
                                        $priorityColor = 'var(--danger-soft)';
                                        break;
                                    case 'critique':
                                    case 'urgente':
                                        $priorityColor = '#dc2626';
                                        break;
                                    default:
                                        $priorityColor = 'var(--primary-soft)';
                                }
                                ?>
                                <div class="status-indicator" style="background-color: <?= $priorityColor ?>;"></div>
                                <span
                                    class="text-neutral-700 font-medium"><?= htmlspecialchars($priorite['priorite']) ?></span>
                            </div>
                            <span class="badge"
                                style="background-color: rgba(239, 68, 68, 0.1); color: var(--danger-soft);">
                                <?= number_format($priorite['count']) ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Top Localisations -->
            <div class="stats-card p-6 fade-in" style="animation-delay: 0.9s;">
                <h3 class="section-title">
                    <i class="fas fa-map-marker-alt"
                        style="background: linear-gradient(135deg, var(--accent-soft), var(--secondary-soft)); color: white;"></i>
                    Localisations Fréquentes
                </h3>
                <div class="space-y-3">
                    <?php foreach ($localisations as $index => $localisation): ?>
                        <div class="list-item">
                            <div class="flex items-center">
                                <div class="w-6 h-6 rounded-full flex items-center justify-center text-white text-xs font-bold mr-3"
                                    style="background: linear-gradient(135deg, var(--accent-soft), var(--secondary-soft));">
                                    <?= $index + 1 ?>
                                </div>
                                <span
                                    class="text-neutral-700 font-medium"><?= htmlspecialchars($localisation['localisation']) ?></span>
                            </div>
                            <span class="badge"
                                style="background-color: rgba(16, 185, 129, 0.1); color: var(--accent-soft);">
                                <?= number_format($localisation['count']) ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Utilisateurs les plus actifs (admin/modérateur uniquement) -->
            <?php if (!empty($top_users)): ?>
                <div class="stats-card p-6 fade-in" style="animation-delay: 1s;">
                    <h3 class="section-title">
                        <i class="fas fa-star"
                            style="background: linear-gradient(135deg, #fbbf24, var(--warning-soft)); color: white;"></i>
                        Utilisateurs Actifs
                    </h3>
                    <div class="space-y-3">
                        <?php foreach ($top_users as $index => $user): ?>
                            <div class="list-item">
                                <div class="flex items-center">
                                    <div class="w-6 h-6 rounded-full flex items-center justify-center text-white text-xs font-bold mr-3"
                                        style="background: linear-gradient(135deg, #fbbf24, var(--warning-soft));">
                                        <?= $index + 1 ?>
                                    </div>
                                    <span
                                        class="text-neutral-700 font-medium"><?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?></span>
                                </div>
                                <span class="badge" style="background-color: rgba(251, 191, 36, 0.1); color: #d97706;">
                                    <?= number_format($user['count']) ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Bouton de retour moderne -->
        <div class="text-center mt-12">
            <a href="../index.php" class="btn-modern">
                <i class="fas fa-arrow-left"></i>
                Retour à l'accueil
            </a>
        </div>
    </div>

    <script>
        // Animation d'entrée séquentielle améliorée
        document.addEventListener('DOMContentLoaded', function () {
            const elements = document.querySelectorAll('.fade-in');

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            }, {
                threshold: 0.1
            });

            elements.forEach(el => {
                el.style.opacity = '0';
                el.style.transform = 'translateY(20px)';
                el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                observer.observe(el);
            });
        });
    </script>
    <?php require_once '../Inc/Components/footers.php'; ?>
    <?php require_once '../Inc/Components/footer.php'; ?>
    <?php require_once '../Inc/Traitement/create_log.php'; ?>