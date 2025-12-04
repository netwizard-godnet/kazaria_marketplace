<?php
use App\Models\Setting;
use App\Models\Category;
use App\Models\Subcategory;
use Illuminate\Support\Facades\Storage;
?>



<?php $__env->startSection('title', 'Paramètres du site'); ?>

<?php $__env->startSection('content'); ?>
<div class="page-inner">
    <div class="page-header">
        <h4 class="page-title">Paramètres du site</h4>
        <ul class="breadcrumbs">
            <li class="nav-home">
                <a href="<?php echo e(route('admin.dashboard')); ?>"><i class="flaticon-home"></i></a>
            </li>
            <li class="separator"><i class="flaticon-right-arrow"></i></li>
            <li class="nav-item"><span>Paramètres</span></li>
        </ul>
    </div>

    
    <?php if(session('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i><?php echo e(session('success')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if(session('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i><?php echo e(session('error')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if($errors->any()): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>Les paramètres n'ont pas pu être mis à jour :
            <ul class="mb-0 mt-2">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?php echo e(route('admin.settings.update')); ?>" enctype="multipart/form-data">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('PUT'); ?>
                        
                                <div class="row">
            <?php
                // Définir l'ordre d'affichage des groupes
                $groupOrder = ['general', 'contact', 'ecommerce', 'deals', 'social', 'maintenance', 'cinetpay', 'stripe'];
                // Réorganiser les groupes selon l'ordre défini
                $orderedGroups = [];
                foreach ($groupOrder as $orderedGroupName) {
                    if (isset($groups[$orderedGroupName])) {
                        $orderedGroups[$orderedGroupName] = $groups[$orderedGroupName];
                    }
                }
                // Ajouter les groupes qui ne sont pas dans la liste (en fin)
                foreach ($groups as $groupName => $groupSettings) {
                    if (!in_array($groupName, $groupOrder)) {
                        $orderedGroups[$groupName] = $groupSettings;
                    }
                }
            ?>
            
            <?php
                // Séparer les groupes de paiement des autres
                $paymentGroups = [];
                $otherGroups = [];
                foreach ($orderedGroups as $groupName => $groupSettings) {
                    if ($groupName === 'cinetpay' || $groupName === 'stripe') {
                        $paymentGroups[$groupName] = $groupSettings;
                    } else {
                        $otherGroups[$groupName] = $groupSettings;
                    }
                }
            ?>
            
            
            <?php $__currentLoopData = $otherGroups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $groupName => $groupSettings): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $isGeneral = ($groupName === 'general');
                    $columnClass = $isGeneral ? 'col-12' : 'col-md-6';
                ?>
            <div class="<?php echo e($columnClass); ?> mb-4">
                <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">
                            <?php switch($groupName):
                                case ('general'): ?>
                                    <i class="fas fa-cog me-2"></i>Général
                                    <?php break; ?>
                                <?php case ('contact'): ?>
                                    <i class="fas fa-phone me-2"></i>Contact
                                    <?php break; ?>
                                <?php case ('social'): ?>
                                    <i class="fas fa-share-alt me-2"></i>Réseaux sociaux
                                    <?php break; ?>
                                <?php case ('ecommerce'): ?>
                                    <i class="fas fa-shopping-cart me-2"></i>E-commerce
                                    <?php break; ?>
                                <?php case ('deals'): ?>
                                    <i class="fas fa-fire me-2"></i>Deals du jour
                                    <?php break; ?>
                                <?php case ('maintenance'): ?>
                                    <i class="fas fa-tools me-2"></i>Maintenance
                                    <?php break; ?>
                                <?php case ('cinetpay'): ?>
                                    <i class="fas fa-credit-card me-2"></i>CinetPay
                                    <?php break; ?>
                                <?php case ('stripe'): ?>
                                    <i class="fab fa-stripe me-2"></i>Stripe
                                    <?php break; ?>
                                <?php default: ?>
                                    <i class="fas fa-cog me-2"></i><?php echo e(ucfirst($groupName)); ?>

                            <?php endswitch; ?>
                                </h5>
                            </div>
                            <div class="card-body">
                        <?php 
                            $isGeneral = ($groupName === 'general');
                            // Clés traitées comme booléens même si le type n'est pas "boolean" en BDD
                            $booleanKeys = [
                                'email_notifications',
                                'push_notifications',
                                'maintenance_mode',
                            ];
                        ?>
                        <?php if($isGeneral): ?>
                                <div class="row">
                        <?php endif; ?>
                        <?php $__currentLoopData = $groupSettings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $setting): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<?php
    $label = $setting->description ?: match($setting->key) {
                                // Général
                                'site_name' => 'Nom du site',
                                'site_description' => 'Description du site',
                                'site_keywords' => 'Mots-clés SEO',
                                'site_logo' => 'Logo du site',
                                'site_favicon' => 'Favicon du site',
                                // Contact
                                'contact_email' => 'Email de contact',
                                'contact_phone' => 'Téléphone de contact',
                                'contact_address' => 'Adresse de contact',
                                // E‑commerce
                                'currency' => 'Devise (code)',
                                'currency_symbol' => 'Symbole de la devise',
                                'min_order_quantity' => 'Quantité minimale de commande',
                                'shipping_cost' => 'Coût de livraison (FCFA)',
                                'free_shipping_threshold' => 'Seuil de livraison gratuite (FCFA)',
                                // Deals
                                'deals_countdown_duration' => 'Durée du countdown des deals (en minutes)',
                                'deals_min_discount' => 'Pourcentage de remise minimum (%)',
                                'deals_max_discount' => 'Pourcentage de remise maximum (%)',
                                'deals_categories' => 'Catégories des deals',
                                'deals_subcategories' => 'Sous-catégories des deals',
                                // Réseaux sociaux
                                'social_facebook' => 'Page Facebook',
                                'social_twitter' => 'Compte Twitter/X',
                                'social_instagram' => 'Compte Instagram',
                                'social_linkedin' => 'Page LinkedIn',
                                // Maintenance
                                'maintenance_mode' => 'Mode maintenance',
                                'maintenance_message' => 'Message de maintenance',
                                // Paiement (CinetPay)
                                'cinetpay_api_key' => 'Clé API CinetPay',
                                'cinetpay_site_id' => 'ID du site CinetPay',
                                'cinetpay_currency' => 'Devise CinetPay',
                                // Paiement (Stripe)
                                'stripe_public_key' => 'Clé publique Stripe',
                                'stripe_secret_key' => 'Clé secrète Stripe',
                                // Autres paramètres (généraux / sécurité / email / commandes)
                                'default_commission_rate' => 'Taux de commission par défaut (%)',
                                'default_shipping_cost' => 'Frais de livraison par défaut (FCFA)',
                                'mail_from_address' => 'Adresse expéditeur (email)',
                                'mail_from_name' => 'Nom expéditeur (email)',
                                'mail_support_address' => 'Adresse support (email)',
                                'max_login_attempts' => 'Tentatives de connexion max',
                                'min_order_amount' => 'Montant minimum de commande (FCFA)',
                                'password_min_length' => 'Longueur minimale du mot de passe',
                                'push_notifications' => 'Notifications push',
                                'site_address' => 'Adresse du site',
                                'site_email' => 'Email du site',
                                'site_phone' => 'Téléphone du site',
                                default => ucfirst(str_replace('_', ' ', $setting->key)),
                            };
                        ?>
                        <div class="form-group mb-3 <?php echo e($isGeneral ? 'col-md-6' : ''); ?>">
                            <label for="setting_<?php echo e($setting->key); ?>" class="form-label">
                                <?php echo e($label); ?>

                                <?php if($setting->is_public): ?>
                                    <span class="badge badge-success badge-sm">Public</span>
                                <?php endif; ?>
                            </label>
                            
                            <?php if($setting->key === 'deals_categories'): ?>
                                <select class="form-control" id="setting_<?php echo e($setting->key); ?>" name="settings[<?php echo e($setting->key); ?>]" multiple>
                                    <option value="">Toutes les catégories</option>
                                    <?php $__currentLoopData = \App\Models\Category::active()->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($category->id); ?>" <?php echo e(in_array($category->id, $setting->value ? explode(',', $setting->value) : []) ? 'selected' : ''); ?>>
                                            <?php echo e($category->name); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <small class="text-muted">Sélectionnez plusieurs catégories en maintenant Ctrl (Cmd sur Mac)</small>
                            <?php elseif($setting->key === 'deals_subcategories'): ?>
                                <select class="form-control" id="setting_<?php echo e($setting->key); ?>" name="settings[<?php echo e($setting->key); ?>]" multiple>
                                    <option value="">Toutes les sous-catégories</option>
                                    <?php $__currentLoopData = \App\Models\Subcategory::active()->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subcategory): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($subcategory->id); ?>" <?php echo e(in_array($subcategory->id, $setting->value ? explode(',', $setting->value) : []) ? 'selected' : ''); ?>>
                                            <?php echo e($subcategory->name); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <small class="text-muted">Sélectionnez plusieurs sous-catégories en maintenant Ctrl (Cmd sur Mac)</small>
                            <?php elseif($setting->type === 'boolean' || in_array($setting->key, $booleanKeys)): ?>
                                <div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio"
                                               id="setting_<?php echo e($setting->key); ?>_1"
                                               name="settings[<?php echo e($setting->key); ?>]"
                                               value="1" <?php echo e(((string)$setting->value === '1') ? 'checked' : ''); ?>>
                                        <label class="form-check-label" for="setting_<?php echo e($setting->key); ?>_1">Oui</label>
                            </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio"
                                               id="setting_<?php echo e($setting->key); ?>_0"
                                               name="settings[<?php echo e($setting->key); ?>]"
                                               value="0" <?php echo e(((string)$setting->value === '0') ? 'checked' : ''); ?>>
                                        <label class="form-check-label" for="setting_<?php echo e($setting->key); ?>_0">Non</label>
                                        </div>
                                    </div>
                            <?php elseif($setting->type === 'integer' || $setting->type === 'float'): ?>
                                <input type="number" 
                                       class="form-control" 
                                       id="setting_<?php echo e($setting->key); ?>" 
                                       name="settings[<?php echo e($setting->key); ?>]" 
                                       value="<?php echo e($setting->value); ?>"
                                       step="<?php echo e($setting->type === 'float' ? '0.01' : '1'); ?>">
                            <?php elseif($setting->type === 'array' || $setting->type === 'json'): ?>
                                <textarea class="form-control" 
                                          id="setting_<?php echo e($setting->key); ?>" 
                                          name="settings[<?php echo e($setting->key); ?>]" 
                                          rows="3"><?php echo e(is_array($setting->value) ? json_encode($setting->value, JSON_PRETTY_PRINT) : $setting->value); ?></textarea>
                            <?php else: ?>
                                <input type="text" 
                                       class="form-control" 
                                       id="setting_<?php echo e($setting->key); ?>" 
                                       name="settings[<?php echo e($setting->key); ?>]" 
                                       value="<?php echo e($setting->value); ?>">
                            <?php endif; ?>
                                        </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php if($isGeneral): ?>
                                    </div>
                        <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            
            
            <?php if(count($paymentGroups) > 0): ?>
                <div class="col-12 mb-4">
                    <h6 class="text-muted mb-3">
                        <i class="fas fa-money-bill-wave me-2"></i>Configuration des passerelles de paiement
                    </h6>
                </div>
                <?php $__currentLoopData = $paymentGroups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $groupName => $groupSettings): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="col-md-6 mb-4">
                <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">
                            <?php switch($groupName):
                                case ('cinetpay'): ?>
                                    <i class="fas fa-credit-card me-2"></i>CinetPay
                                    <?php break; ?>
                                <?php case ('stripe'): ?>
                                    <i class="fab fa-stripe me-2"></i>Stripe
                                    <?php break; ?>
                                <?php default: ?>
                                    <i class="fas fa-cog me-2"></i><?php echo e(ucfirst($groupName)); ?>

                            <?php endswitch; ?>
                                </h5>
                            </div>
                            <div class="card-body">
                        <?php 
                            $isGeneral = false; // Jamais général pour les paiements
                            // Clés traitées comme booléens même si le type n'est pas "boolean" en BDD
                            $booleanKeys = [
                                'email_notifications',
                                'push_notifications',
                                'maintenance_mode',
                            ];
                        ?>
                        <?php $__currentLoopData = $groupSettings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $setting): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<?php
    $label = $setting->description ?: match($setting->key) {
                                // Général
                                'site_name' => 'Nom du site',
                                'site_description' => 'Description du site',
                                'site_keywords' => 'Mots-clés SEO',
                                'site_logo' => 'Logo du site',
                                'site_favicon' => 'Favicon du site',
                                // Contact
                                'contact_email' => 'Email de contact',
                                'contact_phone' => 'Téléphone de contact',
                                'contact_address' => 'Adresse de contact',
                                // E‑commerce
                                'currency' => 'Devise (code)',
                                'currency_symbol' => 'Symbole de la devise',
                                'min_order_quantity' => 'Quantité minimale de commande',
                                'shipping_cost' => 'Coût de livraison (FCFA)',
                                'free_shipping_threshold' => 'Seuil de livraison gratuite (FCFA)',
                                // Deals
                                'deals_countdown_duration' => 'Durée du countdown des deals (en minutes)',
                                'deals_min_discount' => 'Pourcentage de remise minimum (%)',
                                'deals_max_discount' => 'Pourcentage de remise maximum (%)',
                                'deals_categories' => 'Catégories des deals',
                                'deals_subcategories' => 'Sous-catégories des deals',
                                // Réseaux sociaux
                                'social_facebook' => 'Page Facebook',
                                'social_twitter' => 'Compte Twitter/X',
                                'social_instagram' => 'Compte Instagram',
                                'social_linkedin' => 'Page LinkedIn',
                                // Maintenance
                                'maintenance_mode' => 'Mode maintenance',
                                'maintenance_message' => 'Message de maintenance',
                                // Paiement (CinetPay)
                                'cinetpay_api_key' => 'Clé API CinetPay',
                                'cinetpay_site_id' => 'ID du site CinetPay',
                                'cinetpay_currency' => 'Devise CinetPay',
                                // Paiement (Stripe)
                                'stripe_public_key' => 'Clé publique Stripe',
                                'stripe_secret_key' => 'Clé secrète Stripe',
                                // Autres paramètres (généraux / sécurité / email / commandes)
                                'default_commission_rate' => 'Taux de commission par défaut (%)',
                                'default_shipping_cost' => 'Frais de livraison par défaut (FCFA)',
                                'mail_from_address' => 'Adresse expéditeur (email)',
                                'mail_from_name' => 'Nom expéditeur (email)',
                                'mail_support_address' => 'Adresse support (email)',
                                'max_login_attempts' => 'Tentatives de connexion max',
                                'min_order_amount' => 'Montant minimum de commande (FCFA)',
                                'password_min_length' => 'Longueur minimale du mot de passe',
                                'push_notifications' => 'Notifications push',
                                'site_address' => 'Adresse du site',
                                'site_email' => 'Email du site',
                                'site_phone' => 'Téléphone du site',
                                default => ucfirst(str_replace('_', ' ', $setting->key)),
                            };
                        ?>
                        <div class="form-group mb-3">
                            <label for="setting_<?php echo e($setting->key); ?>" class="form-label">
                                <?php echo e($label); ?>

                                <?php if($setting->is_public): ?>
                                    <span class="badge badge-success badge-sm">Public</span>
                                <?php endif; ?>
                            </label>
                            
                            <?php if($setting->type === 'boolean' || in_array($setting->key, $booleanKeys)): ?>
                                <div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio"
                                               id="setting_<?php echo e($setting->key); ?>_1"
                                               name="settings[<?php echo e($setting->key); ?>]"
                                               value="1" <?php echo e(((string)$setting->value === '1') ? 'checked' : ''); ?>>
                                        <label class="form-check-label" for="setting_<?php echo e($setting->key); ?>_1">Oui</label>
                            </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio"
                                               id="setting_<?php echo e($setting->key); ?>_0"
                                               name="settings[<?php echo e($setting->key); ?>]"
                                               value="0" <?php echo e(((string)$setting->value === '0') ? 'checked' : ''); ?>>
                                        <label class="form-check-label" for="setting_<?php echo e($setting->key); ?>_0">Non</label>
                                        </div>
                                    </div>
                            <?php elseif($setting->type === 'integer' || $setting->type === 'float'): ?>
                                <input type="number" 
                                       class="form-control" 
                                       id="setting_<?php echo e($setting->key); ?>" 
                                       name="settings[<?php echo e($setting->key); ?>]" 
                                       value="<?php echo e($setting->value); ?>"
                                       step="<?php echo e($setting->type === 'float' ? '0.01' : '1'); ?>">
                            <?php elseif($setting->type === 'array' || $setting->type === 'json'): ?>
                                <textarea class="form-control" 
                                          id="setting_<?php echo e($setting->key); ?>" 
                                          name="settings[<?php echo e($setting->key); ?>]" 
                                          rows="3"><?php echo e(is_array($setting->value) ? json_encode($setting->value, JSON_PRETTY_PRINT) : $setting->value); ?></textarea>
                            <?php else: ?>
                                <input type="text" 
                                       class="form-control" 
                                       id="setting_<?php echo e($setting->key); ?>" 
                                       name="settings[<?php echo e($setting->key); ?>]" 
                                       value="<?php echo e($setting->value); ?>">
                            <?php endif; ?>
                                        </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </div>
                                    </div>
                                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php endif; ?>
                        </div>

        <!-- Section spéciale pour les fichiers -->
                                <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">
                            <i class="fas fa-image me-2"></i>Images
                                </h5>
                            </div>
                    <div class="card-body bg-success-subtle">
                        <div class="form-group mb-3">
                            <label for="logo" class="form-label">Logo du site</label>
                            <input type="file" class="form-control" id="logo" name="logo" accept="image/*">
                            <?php $siteLogo = Setting::get('site_logo'); ?>
                            <?php if($siteLogo): ?>
                                <div class="mt-2">
                                    <?php 
                                        $logoPath = ltrim($siteLogo, '/');
                                        $logoExists = Storage::disk('public')->exists($logoPath);
                                        // Ajouter un timestamp pour cache-busting
                                        $logoUrl = $logoExists ? asset('storage/' . $logoPath . '?v=' . time()) : null;
                                    ?>
                                    <?php if($logoExists): ?>
                                    <img src="<?php echo e($logoUrl); ?>" 
                                         alt="Logo actuel" 
                                         style="max-height: 50px;"
                                         class="img-thumbnail"
                                         onerror="this.onerror=null; this.src='<?php echo e(asset('images/logo.png')); ?>';">
                                    <?php else: ?>
                                    <small class="text-danger d-block">Fichier introuvable: <code><?php echo e($siteLogo); ?></code></small>
                                    <?php endif; ?>
                                    <small class="text-muted d-block">Logo actuel</small>
                                        </div>
                            <?php endif; ?>
                        </div>

                        <div class="form-group mb-3">
                            <label for="favicon" class="form-label">Favicon</label>
                            <input type="file" class="form-control" id="favicon" name="favicon" accept="image/*">
                            <?php $siteFavicon = Setting::get('site_favicon'); ?>
                            <?php if($siteFavicon): ?>
                                <div class="mt-2">
                                    <?php 
                                        $faviconPath = ltrim($siteFavicon, '/');
                                        $faviconExists = Storage::disk('public')->exists($faviconPath);
                                        // Ajouter un timestamp pour cache-busting
                                        $faviconUrl = $faviconExists ? asset('storage/' . $faviconPath . '?v=' . time()) : null;
                                    ?>
                                    <?php if($faviconExists): ?>
                                    <img src="<?php echo e($faviconUrl); ?>" 
                                         alt="Favicon actuel" 
                                         style="max-height: 32px;"
                                         class="img-thumbnail"
                                         onerror="this.onerror=null; this.src='<?php echo e(asset('favicon.png')); ?>';">
                                    <?php else: ?>
                                    <small class="text-danger d-block">Fichier introuvable: <code><?php echo e($siteFavicon); ?></code></small>
                                    <?php endif; ?>
                                    <small class="text-muted d-block">Favicon actuel</small>
                            </div>
                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                                <div class="row">
            <div class="col-12">
                        <div class="card">
                    <div class="card-body text-center">
                        <button type="submit" class="btn btn-primary btn-lg me-3">
                            <i class="fas fa-save me-2"></i>Enregistrer les paramètres
                                        </button>
                        <button type="button" class="btn btn-warning btn-lg" onclick="resetSettings()">
                            <i class="fas fa-undo me-2"></i>Réinitialiser
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

<!-- Modal de confirmation pour la réinitialisation -->
<div class="modal fade" id="resetModal" tabindex="-1" aria-labelledby="resetModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="resetModalLabel">Confirmer la réinitialisation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Êtes-vous sûr de vouloir réinitialiser tous les paramètres aux valeurs par défaut ? 
                Cette action est irréversible.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <form method="POST" action="<?php echo e(route('admin.settings.reset')); ?>" class="d-inline">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="btn btn-warning">Réinitialiser</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function resetSettings() {
    const modal = new bootstrap.Modal(document.getElementById('resetModal'));
    modal.show();
}

// Aperçu des images
document.getElementById('logo').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            // Supprimer l'ancien aperçu s'il existe
            const oldPreview = e.target.parentNode.querySelector('div.preview-logo');
            if (oldPreview) {
                oldPreview.remove();
            }
            
            const preview = document.createElement('div');
            preview.className = 'mt-2 preview-logo';
            preview.innerHTML = `
                <img src="${e.target.result}" alt="Aperçu" style="max-height: 50px; border: 2px solid #28a745;">
                <small class="text-success d-block">Aperçu du nouveau logo</small>
            `;
            e.target.parentNode.appendChild(preview);
        };
        reader.readAsDataURL(file);
    }
});

document.getElementById('favicon').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            // Supprimer l'ancien aperçu s'il existe
            const oldPreview = e.target.parentNode.querySelector('div.preview-favicon');
            if (oldPreview) {
                oldPreview.remove();
            }
            
            const preview = document.createElement('div');
            preview.className = 'mt-2 preview-favicon';
            preview.innerHTML = `
                <img src="${e.target.result}" alt="Aperçu" style="max-height: 32px; border: 2px solid #28a745;">
                <small class="text-success d-block">Aperçu du nouveau favicon</small>
            `;
            e.target.parentNode.appendChild(preview);
        };
        reader.readAsDataURL(file);
    }
});

// Recharger les images après soumission du formulaire
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form[method="POST"][action="<?php echo e(route('admin.settings.update')); ?>"]');
    if (form) {
        form.addEventListener('submit', function() {
            // Délai pour permettre la mise à jour du serveur
            setTimeout(function() {
                window.location.reload();
            }, 1500);
        });
    }
});
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\kazaria laravel v0\resources\views\admin\settings\index.blade.php ENDPATH**/ ?>