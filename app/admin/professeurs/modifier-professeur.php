<?php
/**
 * Page : Modifier un Professeur
 * Rôle requis : Admin
 */

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../include/auth_check.php';
require_role('admin');

$pageTitle = 'Modifier Professeur';
$professeur_id = $_GET['id'] ?? null;

if (!$professeur_id) {
    $_SESSION['error_message'] = "Identifiant professeur manquant";
    header('Location: liste-professeurs.php');
    exit;
}

// Récupérer les informations du professeur
try {
    $sql = "
        SELECT 
            prof.id as professeur_id,
            prof.matricule,
            prof.specialite,
            prof.diplome_principal,
            prof.autres_diplomes,
            prof.experience_annees,
            prof.date_recrutement,
            prof.statut_emploi,
            prof.situation_familiale,
            prof.personne_urgence_nom,
            prof.personne_urgence_tel,
            p.id as personne_id,
            p.nom,
            p.prenom,
            p.date_naissance,
            p.lieu_naissance,
            p.sexe,
            p.telephone,
            p.adresse,
            p.nationalite,
            u.id as utilisateur_id,
            u.email,
            u.statut as statut_compte
        FROM professeurs prof
        JOIN personnes p ON prof.personne_id = p.id
        JOIN utilisateurs u ON prof.utilisateur_id = u.id
        WHERE prof.id = :id
        AND prof.deleted_at IS NULL
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $professeur_id]);
    $prof = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$prof) {
        $_SESSION['error_message'] = "Professeur introuvable";
        header('Location: liste-professeurs.php');
        exit;
    }
    
} catch (Exception $e) {
    $_SESSION['error_message'] = "Erreur : " . $e->getMessage();
    header('Location: liste-professeurs.php');
    exit;
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();
        
        // 1. Mettre à jour la table personnes
        $stmtPersonne = $pdo->prepare("
            UPDATE personnes 
            SET nom = :nom,
                prenom = :prenom,
                date_naissance = :date_naissance,
                sexe = :sexe,
                telephone = :telephone,
                adresse = :adresse,
                lieu_naissance = :lieu_naissance,
                nationalite = :nationalite,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = :personne_id
        ");
        
        $stmtPersonne->execute([
            'nom' => strtoupper($_POST['nom']),
            'prenom' => ucwords(strtolower($_POST['prenom'])),
            'date_naissance' => $_POST['date_naissance'],
            'sexe' => $_POST['sexe'],
            'telephone' => $_POST['telephone'],
            'adresse' => $_POST['adresse'] ?? null,
            'lieu_naissance' => $_POST['lieu_naissance'] ?? null,
            'nationalite' => $_POST['nationalite'] ?? 'Malgache',
            'personne_id' => $prof['personne_id']
        ]);
        
        // 2. Mettre à jour l'utilisateur
        $stmtUser = $pdo->prepare("
            UPDATE utilisateurs 
            SET email = :email,
                statut = :statut,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = :utilisateur_id
        ");
        
        $stmtUser->execute([
            'email' => $_POST['email'],
            'statut' => $_POST['statut_compte'],
            'utilisateur_id' => $prof['utilisateur_id']
        ]);
        
        // 3. Mettre à jour le professeur
        $stmtProf = $pdo->prepare("
            UPDATE professeurs 
            SET specialite = :specialite,
                diplome_principal = :diplome_principal,
                autres_diplomes = :autres_diplomes,
                experience_annees = :experience_annees,
                date_recrutement = :date_recrutement,
                statut_emploi = :statut_emploi,
                situation_familiale = :situation_familiale,
                personne_urgence_nom = :personne_urgence_nom,
                personne_urgence_tel = :personne_urgence_tel,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = :professeur_id
        ");
        
        $stmtProf->execute([
            'specialite' => $_POST['specialite'],
            'diplome_principal' => $_POST['diplome_principal'] ?? null,
            'autres_diplomes' => $_POST['autres_diplomes'] ?? null,
            'experience_annees' => $_POST['experience_annees'] ?? 0,
            'date_recrutement' => $_POST['date_recrutement'],
            'statut_emploi' => $_POST['statut_emploi'],
            'situation_familiale' => $_POST['situation_familiale'] ?? null,
            'personne_urgence_nom' => $_POST['personne_urgence_nom'] ?? null,
            'personne_urgence_tel' => $_POST['personne_urgence_tel'] ?? null,
            'professeur_id' => $professeur_id
        ]);
        
        // 4. Changer le mot de passe si fourni
        if (!empty($_POST['nouveau_mot_de_passe'])) {
            $password_hash = password_hash($_POST['nouveau_mot_de_passe'], PASSWORD_DEFAULT);
            $stmtPassword = $pdo->prepare("
                UPDATE utilisateurs 
                SET mot_de_passe = :password_hash 
                WHERE id = :utilisateur_id
            ");
            $stmtPassword->execute([
                'password_hash' => $password_hash,
                'utilisateur_id' => $prof['utilisateur_id']
            ]);
        }
        
        $pdo->commit();
        
        $_SESSION['success_message'] = "✅ Professeur modifié avec succès !";
        header('Location: detail-professeur.php?id=' . $professeur_id);
        exit;
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error_message'] = "Erreur lors de la modification : " . $e->getMessage();
    }
}

require_once __DIR__ . '/../../include/header.php';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier Professeur - CEG FM</title>
    <link rel="stylesheet" href="../../../public/assets/styles/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="parent">
        <div class="div3">
            <!-- En-tête -->
            <div class="page-header">
                <div>
                    <h1><i class="fa-solid fa-pen"></i> Modifier un Professeur</h1>
                    <p class="text-muted">Mise à jour des informations de <?= htmlspecialchars($prof['prenom'] . ' ' . $prof['nom']) ?></p>
                </div>
                <div>
                    <a href="detail-professeur.php?id=<?= $professeur_id ?>" class="btn btn-outline-secondary">
                        <i class="fa-solid fa-arrow-left"></i> Retour au profil
                    </a>
                </div>
            </div>

            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger">
                    <i class="fa-solid fa-exclamation-circle"></i>
                    <?= htmlspecialchars($_SESSION['error_message']) ?>
                </div>
                <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>

            <!-- Formulaire de modification -->
            <form method="POST" id="formModification" class="modification-form">
                
                <!-- Section 1: Informations Personnelles -->
                <div class="form-section">
                    <div class="section-header">
                        <div class="section-icon">
                            <i class="fa-solid fa-user"></i>
                        </div>
                        <div>
                            <h3>Informations Personnelles</h3>
                            <p>Identité et coordonnées du professeur</p>
                        </div>
                    </div>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="nom" class="required">Nom de famille</label>
                            <div class="input-with-icon">
                                <i class="fa-solid fa-user"></i>
                                <input type="text" 
                                       id="nom" 
                                       name="nom" 
                                       class="form-control" 
                                       value="<?= htmlspecialchars($prof['nom']) ?>"
                                       required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="prenom" class="required">Prénom(s)</label>
                            <div class="input-with-icon">
                                <i class="fa-solid fa-user"></i>
                                <input type="text" 
                                       id="prenom" 
                                       name="prenom" 
                                       class="form-control" 
                                       value="<?= htmlspecialchars($prof['prenom']) ?>"
                                       required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="sexe" class="required">Sexe</label>
                            <div class="input-with-icon">
                                <i class="fa-solid fa-venus-mars"></i>
                                <select id="sexe" name="sexe" class="form-control" required>
                                    <option value="M" <?= $prof['sexe'] === 'M' ? 'selected' : '' ?>>Masculin</option>
                                    <option value="F" <?= $prof['sexe'] === 'F' ? 'selected' : '' ?>>Féminin</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="date_naissance" class="required">Date de naissance</label>
                            <div class="input-with-icon">
                                <i class="fa-solid fa-calendar"></i>
                                <input type="date" 
                                       id="date_naissance" 
                                       name="date_naissance" 
                                       class="form-control"
                                       value="<?= htmlspecialchars($prof['date_naissance']) ?>"
                                       max="<?= date('Y-m-d', strtotime('-18 years')) ?>"
                                       required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="lieu_naissance">Lieu de naissance</label>
                            <div class="input-with-icon">
                                <i class="fa-solid fa-map-marker-alt"></i>
                                <input type="text" 
                                       id="lieu_naissance" 
                                       name="lieu_naissance" 
                                       class="form-control" 
                                       value="<?= htmlspecialchars($prof['lieu_naissance'] ?? '') ?>">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="nationalite">Nationalité</label>
                            <div class="input-with-icon">
                                <i class="fa-solid fa-flag"></i>
                                <input type="text" 
                                       id="nationalite" 
                                       name="nationalite" 
                                       class="form-control" 
                                       value="<?= htmlspecialchars($prof['nationalite'] ?? 'Malgache') ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Contact -->
                <div class="form-section">
                    <div class="section-header">
                        <div class="section-icon bg-success">
                            <i class="fa-solid fa-address-book"></i>
                        </div>
                        <div>
                            <h3>Coordonnées</h3>
                            <p>Informations de contact</p>
                        </div>
                    </div>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="telephone" class="required">Téléphone</label>
                            <div class="input-with-icon">
                                <i class="fa-solid fa-phone"></i>
                                <input type="tel" 
                                       id="telephone" 
                                       name="telephone" 
                                       class="form-control" 
                                       value="<?= htmlspecialchars($prof['telephone']) ?>"
                                       pattern="[0-9\s]+"
                                       required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="email" class="required">Email</label>
                            <div class="input-with-icon">
                                <i class="fa-solid fa-envelope"></i>
                                <input type="email" 
                                       id="email" 
                                       name="email" 
                                       class="form-control" 
                                       value="<?= htmlspecialchars($prof['email']) ?>"
                                       required>
                            </div>
                            <small class="form-text">Servira de login pour se connecter</small>
                        </div>
                        
                        <div class="form-group full-width">
                            <label for="adresse">Adresse complète</label>
                            <div class="input-with-icon">
                                <i class="fa-solid fa-home"></i>
                                <textarea id="adresse" 
                                          name="adresse" 
                                          class="form-control" 
                                          rows="2"><?= htmlspecialchars($prof['adresse'] ?? '') ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 3: Informations Professionnelles -->
                <div class="form-section">
                    <div class="section-header">
                        <div class="section-icon bg-warning">
                            <i class="fa-solid fa-graduation-cap"></i>
                        </div>
                        <div>
                            <h3>Qualifications & Expérience</h3>
                            <p>Formation et parcours professionnel</p>
                        </div>
                    </div>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="matricule">Matricule</label>
                            <div class="input-with-icon">
                                <i class="fa-solid fa-id-card"></i>
                                <input type="text" 
                                       id="matricule" 
                                       name="matricule" 
                                       class="form-control" 
                                       value="<?= htmlspecialchars($prof['matricule']) ?>"
                                       readonly>
                            </div>
                            <small class="form-text">Le matricule ne peut pas être modifié</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="specialite" class="required">Spécialité</label>
                            <div class="input-with-icon">
                                <i class="fa-solid fa-book"></i>
                                <select id="specialite" name="specialite" class="form-control" required>
                                    <option value="">Sélectionner...</option>
                                    <?php
                                    $specialites = [
                                        'Mathématiques', 'Physique-Chimie', 'SVT', 'Français', 'Anglais',
                                        'Malgache', 'Histoire-Géographie', 'Philosophie', 'EPS',
                                        'Arts Plastiques', 'Musique', 'Technologie', 'Informatique', 'Économie'
                                    ];
                                    foreach ($specialites as $spec):
                                    ?>
                                        <option value="<?= $spec ?>" <?= $prof['specialite'] === $spec ? 'selected' : '' ?>>
                                            <?= $spec ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="diplome_principal" class="required">Diplôme principal</label>
                            <div class="input-with-icon">
                                <i class="fa-solid fa-certificate"></i>
                                <select id="diplome_principal" name="diplome_principal" class="form-control" required>
                                    <option value="">Sélectionner...</option>
                                    <?php
                                    $diplomes = ['Licence', 'Master', 'Doctorat', 'CAPEN', 'Agrégation', 'Autre'];
                                    foreach ($diplomes as $dip):
                                    ?>
                                        <option value="<?= $dip ?>" <?= $prof['diplome_principal'] === $dip ? 'selected' : '' ?>>
                                            <?= $dip ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="experience_annees">Années d'expérience</label>
                            <div class="input-with-icon">
                                <i class="fa-solid fa-briefcase"></i>
                                <input type="number" 
                                       id="experience_annees" 
                                       name="experience_annees" 
                                       class="form-control" 
                                       min="0" 
                                       max="50"
                                       value="<?= htmlspecialchars($prof['experience_annees'] ?? 0) ?>">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="date_recrutement" class="required">Date de recrutement</label>
                            <div class="input-with-icon">
                                <i class="fa-solid fa-calendar-check"></i>
                                <input type="date" 
                                       id="date_recrutement" 
                                       name="date_recrutement" 
                                       class="form-control"
                                       value="<?= htmlspecialchars($prof['date_recrutement']) ?>"
                                       required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="statut_emploi" class="required">Type d'emploi</label>
                            <div class="input-with-icon">
                                <i class="fa-solid fa-id-badge"></i>
                                <select id="statut_emploi" name="statut_emploi" class="form-control" required>
                                    <option value="permanent" <?= $prof['statut_emploi'] === 'permanent' ? 'selected' : '' ?>>Permanent</option>
                                    <option value="contractuel" <?= $prof['statut_emploi'] === 'contractuel' ? 'selected' : '' ?>>Contractuel</option>
                                    <option value="vacataire" <?= $prof['statut_emploi'] === 'vacataire' ? 'selected' : '' ?>>Vacataire</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group full-width">
                            <label for="autres_diplomes">Autres diplômes / Certifications</label>
                            <div class="input-with-icon">
                                <i class="fa-solid fa-award"></i>
                                <textarea id="autres_diplomes" 
                                          name="autres_diplomes" 
                                          class="form-control" 
                                          rows="2"><?= htmlspecialchars($prof['autres_diplomes'] ?? '') ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 4: Informations Complémentaires -->
                <div class="form-section">
                    <div class="section-header">
                        <div class="section-icon bg-info">
                            <i class="fa-solid fa-info-circle"></i>
                        </div>
                        <div>
                            <h3>Informations Complémentaires</h3>
                            <p>Situation familiale et personne à contacter</p>
                        </div>
                    </div>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="situation_familiale">Situation familiale</label>
                            <div class="input-with-icon">
                                <i class="fa-solid fa-heart"></i>
                                <select id="situation_familiale" name="situation_familiale" class="form-control">
                                    <option value="">Sélectionner...</option>
                                    <?php
                                    $situations = ['Célibataire', 'Marié(e)', 'Divorcé(e)', 'Veuf/Veuve'];
                                    foreach ($situations as $sit):
                                    ?>
                                        <option value="<?= $sit ?>" <?= $prof['situation_familiale'] === $sit ? 'selected' : '' ?>>
                                            <?= $sit ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="personne_urgence_nom">Personne à contacter (urgence)</label>
                            <div class="input-with-icon">
                                <i class="fa-solid fa-user-shield"></i>
                                <input type="text" 
                                       id="personne_urgence_nom" 
                                       name="personne_urgence_nom" 
                                       class="form-control" 
                                       value="<?= htmlspecialchars($prof['personne_urgence_nom'] ?? '') ?>">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="personne_urgence_tel">Téléphone (urgence)</label>
                            <div class="input-with-icon">
                                <i class="fa-solid fa-phone-volume"></i>
                                <input type="tel" 
                                       id="personne_urgence_tel" 
                                       name="personne_urgence_tel" 
                                       class="form-control" 
                                       value="<?= htmlspecialchars($prof['personne_urgence_tel'] ?? '') ?>"
                                       pattern="[0-9\s]+">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 5: Compte et Sécurité -->
                <div class="form-section">
                    <div class="section-header">
                        <div class="section-icon bg-danger">
                            <i class="fa-solid fa-lock"></i>
                        </div>
                        <div>
                            <h3>Compte et Sécurité</h3>
                            <p>Statut du compte et modification du mot de passe</p>
                        </div>
                    </div>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="statut_compte" class="required">Statut du compte</label>
                            <div class="input-with-icon">
                                <i class="fa-solid fa-toggle-on"></i>
                                <select id="statut_compte" name="statut_compte" class="form-control" required>
                                    <option value="actif" <?= $prof['statut_compte'] === 'actif' ? 'selected' : '' ?>>Actif</option>
                                    <option value="en_attente" <?= $prof['statut_compte'] === 'en_attente' ? 'selected' : '' ?>>En attente</option>
                                    <option value="suspendu" <?= $prof['statut_compte'] === 'suspendu' ? 'selected' : '' ?>>Suspendu</option>
                                    <option value="inactif" <?= $prof['statut_compte'] === 'inactif' ? 'selected' : '' ?>>Inactif</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="nouveau_mot_de_passe">Nouveau mot de passe</label>
                            <div class="input-with-icon">
                                <i class="fa-solid fa-key"></i>
                                <input type="password" 
                                       id="nouveau_mot_de_passe" 
                                       name="nouveau_mot_de_passe" 
                                       class="form-control"
                                       minlength="6">
                            </div>
                            <small class="form-text">Laisser vide pour conserver l'ancien mot de passe</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="confirmer_mot_de_passe">Confirmer le mot de passe</label>
                            <div class="input-with-icon">
                                <i class="fa-solid fa-key"></i>
                                <input type="password" 
                                       id="confirmer_mot_de_passe" 
                                       name="confirmer_mot_de_passe" 
                                       class="form-control">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Boutons d'action -->
                <div class="form-actions">
                    <button type="button" onclick="history.back()" class="btn btn-outline-secondary btn-lg">
                        <i class="fa-solid fa-times"></i> Annuler
                    </button>
                    <button type="reset" class="btn btn-outline-warning btn-lg">
                        <i class="fa-solid fa-rotate-right"></i> Réinitialiser
                    </button>
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fa-solid fa-check"></i> Enregistrer les modifications
                    </button>
                </div>
            </form>

        </div>
    </div>

    <style>
        :root {
            --primary: #4F46E5;
            --success: #10B981;
            --warning: #F59E0B;
            --danger: #EF4444;
            --info: #3B82F6;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #E5E7EB;
        }

        .page-header h1 {
            font-size: 2rem;
            margin: 0;
        }

        /* Form Sections */
        .form-section {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .section-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #F9FAFB;
        }

        .section-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--primary), #6366F1);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .section-icon.bg-success { background: linear-gradient(135deg, var(--success), #34D399); }
        .section-icon.bg-warning { background: linear-gradient(135deg, var(--warning), #FBBF24); }
        .section-icon.bg-info { background: linear-gradient(135deg, var(--info), #60A5FA); }
        .section-icon.bg-danger { background: linear-gradient(135deg, var(--danger), #F87171); }

        .section-header h3 {
            margin: 0;
            font-size: 1.5rem;
        }

        .section-header p {
            margin: 0.25rem 0 0 0;
            color: #6B7280;
            font-size: 0.9rem;
        }

        /* Form Grid */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-group label {
            font-weight: 600;
            color: #111827;
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
        }

        .form-group label.required::after {
            content: ' *';
            color: var(--danger);
        }

        .input-with-icon {
            position: relative;
        }

        /* .input-with-icon i {
            position: absolute;
            left: 1rem;
            top:0.2px;
        } */
    