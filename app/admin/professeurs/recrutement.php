<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recrutement Professeur - CEG FM</title>
    <link rel="stylesheet" href="../../../public/assets/styles/style.css">
    <link rel="icon" type="image/png" href="../images/icone/CEG-fm.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="parent">
        <?php
        /**
         * Page : Recrutement d'un Professeur
         * Rôle requis : Admin
         */

        // Configuration et protection
        require_once __DIR__ . '/../../config/db.php';
        require_once __DIR__ . '/../../include/auth_check.php';
        require_role('admin');

        $pageTitle = 'Recruter un Professeur';

        // Traitement du formulaire
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $pdo->beginTransaction();
                
                // 1. Insérer dans la table personnes
                $stmtPersonne = $pdo->prepare("
                    INSERT INTO personnes (nom, prenom, date_naissance, sexe, telephone, adresse, lieu_naissance, nationalite)
                    VALUES (:nom, :prenom, :date_naissance, :sexe, :telephone, :adresse, :lieu_naissance, :nationalite)
                ");
                
                $stmtPersonne->execute([
                    'nom' => strtoupper($_POST['nom']),
                    'prenom' => ucwords(strtolower($_POST['prenom'])),
                    'date_naissance' => $_POST['date_naissance'],
                    'sexe' => $_POST['sexe'],
                    'telephone' => $_POST['telephone'],
                    'adresse' => $_POST['adresse'] ?? null,
                    'lieu_naissance' => $_POST['lieu_naissance'] ?? null,
                    'nationalite' => $_POST['nationalite'] ?? 'Malgache'
                ]);
                
                $personne_id = $pdo->lastInsertId();
                
                // 2. Générer le matricule
                $annee = date('Y');
                $stmtCount = $pdo->query("SELECT COUNT(*) FROM professeurs WHERE YEAR(created_at) = $annee");
                $numero = str_pad($stmtCount->fetchColumn() + 1, 4, '0', STR_PAD_LEFT);
                $matricule = "PROF$annee$numero";
                
                // 3. Créer l'utilisateur
                $email = $_POST['email'];
                $password_default = 'Prof@' . date('Y'); // Mot de passe par défaut
                $password_hash = password_hash($password_default, PASSWORD_DEFAULT);
                
                $stmtUser = $pdo->prepare("
                    INSERT INTO utilisateurs (email, mot_de_passe, role, statut)
                    VALUES (:email, :mot_de_passe, 'professeur', 'en_attente')
                ");
                
                $stmtUser->execute([
                    'email' => $email,
                    'mot_de_passe' => $password_hash
                ]);
                
                $utilisateur_id = $pdo->lastInsertId();
                
                // 4. Créer le professeur
                $stmtProf = $pdo->prepare("
                    INSERT INTO professeurs (
                        personne_id, 
                        utilisateur_id, 
                        matricule, 
                        specialite, 
                        date_recrutement,
                        diplome_principal,
                        autres_diplomes,
                        experience_annees,
                        situation_familiale,
                        personne_urgence_nom,
                        personne_urgence_tel
                    )
                    VALUES (
                        :personne_id, 
                        :utilisateur_id, 
                        :matricule, 
                        :specialite, 
                        :date_recrutement,
                        :diplome_principal,
                        :autres_diplomes,
                        :experience_annees,
                        :situation_familiale,
                        :personne_urgence_nom,
                        :personne_urgence_tel
                    )
                ");
                
                $stmtProf->execute([
                    'personne_id' => $personne_id,
                    'utilisateur_id' => $utilisateur_id,
                    'matricule' => $matricule,
                    'specialite' => $_POST['specialite'],
                    'date_recrutement' => $_POST['date_recrutement'] ?? date('Y-m-d'),
                    'diplome_principal' => $_POST['diplome_principal'] ?? null,
                    'autres_diplomes' => $_POST['autres_diplomes'] ?? null,
                    'experience_annees' => $_POST['experience_annees'] ?? 0,
                    'situation_familiale' => $_POST['situation_familiale'] ?? null,
                    'personne_urgence_nom' => $_POST['personne_urgence_nom'] ?? null,
                    'personne_urgence_tel' => $_POST['personne_urgence_tel'] ?? null
                ]);
                
                $pdo->commit();
                
                $_SESSION['success_message'] = "✅ Professeur recruté avec succès ! Matricule : $matricule | Mot de passe : $password_default";
                header('Location: liste-professeurs.php');
                exit;
                
            } catch (Exception $e) {
                $pdo->rollBack();
                $_SESSION['error_message'] = "Erreur lors du recrutement : " . $e->getMessage();
            }
        }

        // Inclure le header
        require_once __DIR__ . '/../../include/header.php';
        ?>
        
        <div class="div3">
            <!-- En-tête -->
            <div class="page-header">
                <div>
                    <h1><i class="fa-solid fa-user-plus"></i> Recruter un Professeur</h1>
                    <p class="text-muted">Enregistrez un nouveau membre du corps enseignant</p>
                </div>
                <a href="liste-professeurs.php" class="btn btn-outline-secondary">
                    <i class="fa-solid fa-arrow-left"></i> Retour à la liste
                </a>
            </div>

            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger alert-dismissible">
                    <i class="fa-solid fa-exclamation-circle"></i>
                    <?= htmlspecialchars($_SESSION['error_message']) ?>
                    <button type="button" class="btn-close" onclick="this.parentElement.remove()"></button>
                </div>
                <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>

            <!-- Formulaire -->
            <form method="POST" id="formRecrutement" class="recruitment-form">
                
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
                                       placeholder="RAKOTO"
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
                                       placeholder="Jean Paul"
                                       required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="sexe" class="required">Sexe</label>
                            <div class="input-with-icon">
                                <i class="fa-solid fa-venus-mars"></i>
                                <select id="sexe" name="sexe" class="form-control" required>
                                    <option value="">Sélectionner...</option>
                                    <option value="M">Masculin</option>
                                    <option value="F">Féminin</option>
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
                                       max="<?= date('Y-m-d', strtotime('-18 years')) ?>"
                                       required>
                            </div>
                            <small class="form-text">Le professeur doit avoir au moins 18 ans</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="lieu_naissance">Lieu de naissance</label>
                            <div class="input-with-icon">
                                <i class="fa-solid fa-map-marker-alt"></i>
                                <input type="text" 
                                       id="lieu_naissance" 
                                       name="lieu_naissance" 
                                       class="form-control" 
                                       placeholder="Antananarivo">
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
                                       value="Malgache">
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
                                       placeholder="034 12 345 67"
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
                                       placeholder="professeur@ceg-fm.mg"
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
                                          rows="2"
                                          placeholder="Lot XXX, Quartier..., Ville..."></textarea>
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
                            <label for="specialite" class="required">Spécialité</label>
                            <div class="input-with-icon">
                                <i class="fa-solid fa-book"></i>
                                <select id="specialite" name="specialite" class="form-control" required>
                                    <option value="">Sélectionner...</option>
                                    <option value="Mathématiques">Mathématiques</option>
                                    <option value="Physique-Chimie">Physique-Chimie</option>
                                    <option value="SVT">Sciences de la Vie et de la Terre</option>
                                    <option value="Français">Français</option>
                                    <option value="Anglais">Anglais</option>
                                    <option value="Malgache">Malgache</option>
                                    <option value="Histoire-Géographie">Histoire-Géographie</option>
                                    <option value="Philosophie">Philosophie</option>
                                    <option value="EPS">Éducation Physique et Sportive</option>
                                    <option value="Arts Plastiques">Arts Plastiques</option>
                                    <option value="Musique">Éducation Musicale</option>
                                    <option value="Technologie">Technologie</option>
                                    <option value="Informatique">Informatique</option>
                                    <option value="Économie">Sciences Économiques</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="diplome_principal" class="required">Diplôme principal</label>
                            <div class="input-with-icon">
                                <i class="fa-solid fa-certificate"></i>
                                <select id="diplome_principal" name="diplome_principal" class="form-control" required>
                                    <option value="">Sélectionner...</option>
                                    <option value="Licence">Licence</option>
                                    <option value="Master">Master</option>
                                    <option value="Doctorat">Doctorat</option>
                                    <option value="CAPEN">CAPEN</option>
                                    <option value="Agrégation">Agrégation</option>
                                    <option value="Autre">Autre</option>
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
                                       value="0">
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
                                       value="<?= date('Y-m-d') ?>"
                                       required>
                            </div>
                        </div>
                        
                        <div class="form-group full-width">
                            <label for="autres_diplomes">Autres diplômes / Certifications</label>
                            <div class="input-with-icon">
                                <i class="fa-solid fa-award"></i>
                                <textarea id="autres_diplomes" 
                                          name="autres_diplomes" 
                                          class="form-control" 
                                          rows="2"
                                          placeholder="Listez les autres diplômes, formations ou certifications..."></textarea>
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
                                    <option value="Célibataire">Célibataire</option>
                                    <option value="Marié(e)">Marié(e)</option>
                                    <option value="Divorcé(e)">Divorcé(e)</option>
                                    <option value="Veuf/Veuve">Veuf/Veuve</option>
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
                                       placeholder="Nom complet">
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
                                       placeholder="034 XX XXX XX"
                                       pattern="[0-9\s]+">
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
                        <i class="fa-solid fa-check"></i> Recruter le professeur
                    </button>
                </div>
            </form>

            <!-- Information Box -->
            <div class="info-box">
                <div class="info-icon">
                    <i class="fa-solid fa-lightbulb"></i>
                </div>
                <div>
                    <h4>📝 Informations importantes</h4>
                    <ul>
                        <li>Un <strong>matricule unique</strong> sera généré automatiquement</li>
                        <li>L'email servira de <strong>login</strong> pour accéder au système</li>
                        <li>Le mot de passe par défaut est : <code>Prof@<?= date('Y') ?></code></li>
                        <li>Le professeur devra <strong>changer son mot de passe</strong> à la première connexion</li>
                        <li>Le statut initial sera "<strong>En attente</strong>" jusqu'à activation</li>
                    </ul>
                </div>
            </div>
        </div>

        <style>
            :root {
                --primary: #4F46E5;
                --success: #10B981;
                --warning: #F59E0B;
                --danger: #EF4444;
                --info: #3B82F6;
                --secondary: #6B7280;
                --light-bg: #F9FAFB;
                --border: #E5E7EB;
                --text-dark: #111827;
                --text-muted: #6B7280;
            }

            .page-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 2rem;
                padding-bottom: 1rem;
                border-bottom: 2px solid var(--border);
            }

            .page-header h1 {
                font-size: 2rem;
                color: var(--text-dark);
                margin: 0;
            }

            .page-header p {
                margin: 0.5rem 0 0 0;
                color: var(--text-muted);
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
                border-bottom: 2px solid var(--light-bg);
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

            .section-icon.bg-success {
                background: linear-gradient(135deg, var(--success), #34D399);
            }

            .section-icon.bg-warning {
                background: linear-gradient(135deg, var(--warning), #FBBF24);
            }

            .section-icon.bg-info {
                background: linear-gradient(135deg, var(--info), #60A5FA);
            }

            .section-header h3 {
                margin: 0;
                font-size: 1.5rem;
                color: var(--text-dark);
            }

            .section-header p {
                margin: 0.25rem 0 0 0;
                color: var(--text-muted);
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
                color: var(--text-dark);
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

            .input-with-icon i {
                position: absolute;
                left: 1rem;
                top: 50%;
                transform: translateY(-50%);
                color: var(--text-muted);
                font-size: 1rem;
            }

            .input-with-icon .form-control {
                padding-left: 3rem;
            }

            .form-control {
                width: 100%;
                padding: 0.75rem 1rem;
                border: 2px solid var(--border);
                border-radius: 8px;
                font-size: 0.95rem;
                transition: all 0.3s;
            }

            .form-control:focus {
                outline: none;
                border-color: var(--primary);
                box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
            }

            textarea.form-control {
                resize: vertical;
            }

            .form-text {
                display: block;
                margin-top: 0.375rem;
                color: var(--text-muted);
                font-size: 0.85rem;
            }

            /* Form Actions */
            .form-actions {
                display: flex;
                justify-content: flex-end;
                gap: 1rem;
                margin-top: 2rem;
                padding-top: 2rem;
                border-top: 2px solid var(--border);
            }

            .btn {
                padding: 0.75rem 2rem;
                border: none;
                border-radius: 8px;
                font-weight: 600;
                font-size: 1rem;
                cursor: pointer;
                transition: all 0.3s;
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
            }

            .btn-lg {
                padding: 1rem 2.5rem;
                font-size: 1.05rem;
            }

            .btn-primary {
                background: linear-gradient(135deg, var(--primary), #6366F1);
                color: white;
            }

            .btn-primary:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(79, 70, 229, 0.4);
            }

            .btn-outline-secondary {
                background: white;
                color: var(--secondary);
                border: 2px solid var(--border);
            }

            .btn-outline-secondary:hover {
                background: var(--light-bg);
            }

            .btn-outline-warning {
                background: white;
                color: var(--warning);
                border: 2px solid var(--warning);
            }

            .btn-outline-warning:hover {
                background: #FEF3C7;
            }

            /* Info Box */
            .info-box {
                background: linear-gradient(135deg, #FEF3C7, #FDE68A);
                border-left: 4px solid var(--warning);
                border-radius: 12px;
                padding: 1.5rem;
                margin-top: 2rem;
                display: flex;
                gap: 1.5rem;
            }

            .info-icon {
                font-size: 2rem;
                color: #D97706;
            }

            .info-box h4 {
                margin: 0 0 1rem 0;
                color: #78350F;
            }

            .info-box ul {
                margin: 0;
                padding-left: 1.25rem;
                color: #92400E;
            }

            .info-box li {
                margin-bottom: 0.5rem;
            }

            .info-box code {
                background: white;
                padding: 0.25rem 0.5rem;
                border-radius: 4px;
                font-weight: 600;
                color: var(--primary);
            }

            /* Alert */
            .alert {
                padding: 1rem 1.5rem;
                border-radius: 8px;
                margin-bottom: 1.5rem;
                display: flex;
                align-items: center;
                gap: 0.75rem;
            }

            .alert-danger {
                background: #FEE2E2;
                color: #991B1B;
                border-left: 4px solid var(--danger);
            }

            .btn-close {
                margin-left: auto;
                background: none;
                border: none;
                font-size: 1.5rem;
                cursor: pointer;
                color: inherit;
                opacity: 0.6;
            }

            .btn-close:hover {
                opacity: 1;
            }

            /* Responsive */
            @media (max-width: 768px) {
                .page-header {
                    flex-direction: column;
                    align-items: flex-start;
                    gap: 1rem;
                }

                .form-grid {
                    grid-template-columns: 1fr;
                }

                .form-actions {
                    flex-direction: column;
                }
                
                .btn {
                    width: 100%;
                    justify-content: center;
                }
            }
        </style>

        <script>
            // Validation du formulaire
            document.getElementById('formRecrutement').addEventListener('submit', function(e) {
                let valid = true;
                const errors = [];
                
                // Validation âge minimum
                const dateNaissance = new Date(document.getElementById('date_naissance').value);
                const age = (new Date() - dateNaissance) / (1000 * 60 * 60 * 24 * 365);
                
                if (age < 18) {
                    errors.push('Le professeur doit avoir au moins 18 ans');
                    valid = false;
                }
                
                // Validation email
                const email = document.getElementById('email').value;
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(email)) {
                    errors.push('Email invalide');
                    valid = false;
                }
                
                // Validation téléphone
                const telephone = document.getElementById('telephone').value;
                if (telephone && !/^[0-9\s]+$/.test(telephone)) {
                    errors.push('Le téléphone ne doit contenir que des chiffres');
                    valid = false;
                }
                
                if (!valid) {
                    e.preventDefault();
                    alert('❌ Erreurs de validation:\n\n' + errors.join('\n'));
                }
            });
            
            // Auto-majuscule pour le nom
            document.getElementById('nom').addEventListener('input', function(e) {
                e.target.value = e.target.value.toUpperCase();
            });
            
            // Auto-capitalize pour le prénom
            document.getElementById('prenom').addEventListener('input', function(e) {
                e.target.value = e.target.value.split(' ')
                    .map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
                    .join(' ');
            });
            
            // Confirmation avant réinitialisation
            document.querySelector('button[type="reset"]').addEventListener('click', function(e) {
                if (!confirm('⚠️ Êtes-vous sûr de vouloir réinitialiser le formulaire ?')) {
                    e.preventDefault();
                }
            });
            
            // Indicateur de progression
            const requiredFields = document.querySelectorAll('[required]');
            let completedFields = 0;
            
            requiredFields.forEach(field => {
                field.addEventListener('input', updateProgress);
            });
            
            function updateProgress() {
                completedFields = 0;
                requiredFields.forEach(field => {
                    if (field.value.trim() !== '') completedFields++;
                });
                
                const progress = (completedFields / requiredFields.length) * 100;
                console.log(`Progression: ${progress.toFixed(0)}%`);
            }
        </script>

        
    </div>
</body>
</html>