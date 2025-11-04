@if(config('kazar_ai.enabled'))
<div id="kazarAIWidget" class="position-fixed kazar-ai-wrap z-index-9x">
    <div class="card shadow kazar-ai-chat z-index-9x" id="kazarAIChat" style="display:none;">
        <div class="card-header d-flex justify-content-between align-items-center orange-bg text-white">
            <strong class="text-white">KAZAR I.A</strong>
            <button class="btn btn-sm btn-outline-secondary" onclick="document.getElementById('kazarAIChat').style.display='none'">×</button>
        </div>
        <div class="card-body kazar-ai-thread" id="kazarAIThread">
            <div class="text-muted small">Bonjour ! Dites‑moi votre besoin (ex: "J'ai 30.000 FCFA et je veux un téléphone 128GB").</div>
        </div>
        <div class="card-footer">
            <div class="input-group">
                <input id="kazarAIInput" type="text" class="form-control" placeholder="Votre message...">
                <button class="btn orange-bg text-white" id="kazarAISend">Envoyer</button>
            </div>
        </div>
    </div>
    <button id="kazarAIOpen" class="btn btn-lg orange-bg text-white rounded-circle shadow kazar-ai-fab">
        <i class="bi bi-robot"></i>
    </button>
</div>

<script>
// Fonction globale pour ajouter des bulles dans le chat
function appendBubble(text, who, isHtml = false){
    const thread = document.getElementById('kazarAIThread');
    if(!thread) return; // Si le chat n'est pas initialisé, ne rien faire
    const div = document.createElement('div');
    div.className = 'mb-2 ' + (who==='me' ? 'text-end' : '');
    const bubble = document.createElement('div');
    bubble.className = 'kazar-ai-bubble '+(who==='me'?'me':'ai');
    if(isHtml){
        bubble.innerHTML = text; // Pour HTML
    } else {
        bubble.textContent = text; // Préserver les \n avec CSS pre-wrap
    }
    div.appendChild(bubble);
    thread.appendChild(div);
    thread.scrollTop = thread.scrollHeight;
}

// Fonction globale pour les headers HTTP
function baseHeaders(json=true){
    const h = { 'Accept':'application/json' };
    if(json) h['Content-Type'] = 'application/json';
    const m = document.querySelector('meta[name="csrf-token"]');
    if(m && m.content) h['X-CSRF-TOKEN'] = m.content;
    return h;
}

// Fonction pour mettre à jour le compteur de panier dans le header
async function updateCartCount(){
    try{
        const headers = (typeof getHeaders === 'function') ? getHeaders() : {'Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]')?.content};
        const res = await fetch('/cart/get', {headers});
        const data = await res.json();
        const count = data.count || data.cart_count || 0;
        if(data.success){
            const cartCounts = document.querySelectorAll('.cart-count');
            cartCounts.forEach(el => {
                el.textContent = count;
                el.style.display = count > 0 ? 'block' : 'none';
            });
        }
    }catch(e){ /* silencieux */ }
}

// Fonction pour afficher une notification toast
function showNotification(message, type = 'success'){
    // Créer un élément de notification
    const toast = document.createElement('div');
    toast.className = `alert alert-${type === 'success' ? 'success' : 'danger'} position-fixed top-0 start-50 translate-middle-x mt-3`;
    toast.style.zIndex = '999999';
    toast.style.minWidth = '300px';
    toast.innerHTML = `
        <div class="d-flex align-items-center">
            <span>${message}</span>
            <button type="button" class="btn-close ms-auto" onclick="this.parentElement.parentElement.remove()"></button>
        </div>
    `;
    document.body.appendChild(toast);
    
    // Auto-suppression après 3 secondes
    setTimeout(() => {
        toast.style.transition = 'opacity 0.3s';
        toast.style.opacity = '0';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

async function getCartProductIds(){
    try{
        const headers = (typeof getHeaders === 'function') ? getHeaders() : {
            'Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content
        };
        const res = await fetch('/cart/get', {headers});
        const data = await res.json();
        if(data && data.items){
            const set = new Set();
            data.items.forEach(i=> { if(i.product && i.product.id) set.add(i.product.id); });
            return set;
        }
    }catch(e){}
    return new Set();
}

async function aiChangeQty(productId, delta){
    try{
        const headers = (typeof getHeaders === 'function') ? getHeaders() : {
            'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content
        };
        const cartRes = await fetch('/cart/get', {headers});
        const cart = await cartRes.json();
        if(!cart.success){ appendBubble('Panier non disponible.', 'ai'); return; }
        const item = (cart.items||[]).find(i=> i.product && i.product.id === productId);
        if(!item){ appendBubble('Article non présent dans le panier.', 'ai'); return; }
        const newQty = Math.max(1, (item.quantity||1) + delta);
        const res = await fetch('/cart/update', {method:'PUT', headers, body: JSON.stringify({ item_id: item.id, quantity: newQty })});
        let data;
        try {
            data = await res.json();
        } catch(e) {
            data = { success: false, message: 'Erreur serveur. Veuillez réessayer.' };
        }
        
        if(res.ok && data.success){
            appendBubble(`Quantité mise à jour: ${newQty}<br><a href="/panier" class="btn btn-sm orange-bg text-white mt-2" style="text-decoration:none;">Voir mon panier</a>`, 'ai', true);
            updateCartCount();
            showNotification(`Quantité mise à jour : ${newQty}`, 'success');
        } else {
            const errorMsg = data.message || (res.status === 404 ? 'Article non trouvé dans votre panier.' : 'Impossible de mettre à jour.');
            appendBubble(errorMsg, 'ai');
            showNotification(errorMsg, 'danger');
            updateCartCount();
        }
    }catch(e){
        appendBubble('Erreur de mise à jour du panier. Veuillez réessayer.', 'ai');
        showNotification('Erreur de mise à jour du panier. Veuillez réessayer.', 'danger');
        updateCartCount();
    }
}

async function aiRemoveFromCart(productId){
    try{
        const headers = (typeof getHeaders === 'function') ? getHeaders() : {
            'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content
        };
        const cartRes = await fetch('/cart/get', {headers});
        const cart = await cartRes.json();
        if(!cart.success){ appendBubble('Panier non disponible.', 'ai'); return; }
        const item = (cart.items||[]).find(i=> i.product && i.product.id === productId);
        if(!item){ appendBubble('Article non présent dans le panier.', 'ai'); return; }
        const res = await fetch('/cart/remove', {method:'DELETE', headers, body: JSON.stringify({ item_id: item.id })});
        let data;
        try {
            data = await res.json();
        } catch(e) {
            // Si la réponse n'est pas du JSON valide
            data = { success: false, message: 'Erreur serveur. Veuillez réessayer.' };
        }
        
        if(res.ok && data.success){
            appendBubble(`Article retiré du panier 🗑️<br><a href="/panier" class="btn btn-sm orange-bg text-white mt-2" style="text-decoration:none;">Voir mon panier</a>`, 'ai', true);
            updateCartCount();
            showNotification('Article retiré du panier', 'success');
        } else {
            const errorMsg = data.message || (res.status === 404 ? 'Article non trouvé dans votre panier.' : 'Impossible de retirer.');
            appendBubble(errorMsg, 'ai');
            showNotification(errorMsg, 'danger');
            // Rafraîchir le compteur au cas où
            updateCartCount();
        }
    }catch(e){
        appendBubble('Erreur lors du retrait. Veuillez réessayer.', 'ai');
        showNotification('Erreur lors du retrait. Veuillez réessayer.', 'danger');
        updateCartCount();
    }
}

async function aiAddToCart(productId){
    try{
        const headers = (typeof getHeaders === 'function') ? getHeaders() : {
            'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content
        };
        const res = await fetch('/cart/add', {method:'POST', headers, body: JSON.stringify({ product_id: productId, quantity: 1 })});
        const data = await res.json();
        if(data.success){
            appendBubble(`Article ajouté au panier ✅<br><a href="/panier" class="btn btn-sm orange-bg text-white mt-2" style="text-decoration:none;">Voir mon panier</a>`, 'ai', true);
            logAiInteraction('add_to_cart', productId);
            updateCartCount();
            showNotification('Article ajouté au panier', 'success');
        }else{
            appendBubble(data.message || "Impossible d'ajouter au panier", 'ai');
            showNotification(data.message || "Impossible d'ajouter au panier", 'danger');
        }
    }catch(e){
        appendBubble("Erreur lors de l'ajout au panier.", 'ai');
        showNotification("Erreur lors de l'ajout au panier.", 'danger');
    }
}

async function logAiInteraction(type, productId){
    try{
        const headers = (typeof getHeaders === 'function') ? getHeaders() : (typeof baseHeaders==='function'? baseHeaders(true): {'Accept':'application/json'});
        await fetch('/api/ai/interaction', { method:'POST', headers, credentials:'same-origin', body: JSON.stringify({ type, product_id: productId }) });
    }catch(e){ /* silencieux */ }
}

document.addEventListener('DOMContentLoaded', function(){
    const openBtn = document.getElementById('kazarAIOpen');
    const chat = document.getElementById('kazarAIChat');
    const sendBtn = document.getElementById('kazarAISend');
    const input = document.getElementById('kazarAIInput');
    const thread = document.getElementById('kazarAIThread');

    openBtn.addEventListener('click', ()=>{ 
        chat.style.display = chat.style.display==='block' ? 'none' : 'block'; 
        openBtn.classList.toggle('active');
    });

    async function ask(){
        const msg = input.value.trim();
        if(!msg) return;
        appendBubble(msg, 'me');
        input.value='';
        try{
            const res = await fetch('/api/ai/query', { method:'POST', headers: baseHeaders(true), body: JSON.stringify({ message: msg })});
            let data;
            try{ data = await res.json(); }catch(_){ data = { success:false, message: 'Serveur indisponible ('+res.status+').' }; }
            if(data.success){
                appendBubble(data.message, 'ai');
                // Action: appliquer coupon
                if(data.intent === 'apply_coupon'){
                    if(data.action_result && data.action_result.success){
                        appendBubble(`Code appliqué (${data.action_result.discount_percent}%). Réduction estimée: ${new Intl.NumberFormat('fr-FR').format(data.action_result.discount_amount||0)} FCFA`, 'ai');
                    } else if(data.intent_params && !data.intent_params.code){
                        appendBubble('Quel est votre code promo ?', 'ai');
                    }
                }
                // Action: suivi commande
                if(data.intent === 'track_order'){
                    const onum = data.intent_params && data.intent_params.order_number;
                    if(onum){
                        appendBubble(`Je vérifie le statut de la commande ${onum}...`, 'ai');
                        fetch(`/api/orders/${onum}`, {headers:{'Accept':'application/json'}, credentials:'same-origin'})
                            .then(r=>r.json())
                            .then(o=>{
                                if(o.success && o.order){
                                    appendBubble(`Statut: ${o.order.status}\nTotal: ${new Intl.NumberFormat('fr-FR').format(o.order.total)} FCFA`, 'ai');
                                } else {
                                    appendBubble(o.message || 'Impossible de récupérer la commande (êtes-vous connecté ?)', 'ai');
                                }
                            })
                            .catch(()=>appendBubble('Erreur lors de la récupération de la commande.', 'ai'));
                    } else {
                        appendBubble('Quel est votre numéro de commande (ex: KAZ-20251103-XXXXXX) ?', 'ai');
                    }
                }
                // Produits + actions panier
                if(Array.isArray(data.items) && data.items.length){
                    const cartIds = await getCartProductIds();
                    const wrap = document.createElement('div');
                    wrap.className = 'mb-2';
                    data.items.forEach(p=>{
                        const card = document.createElement('div');
                        card.className = 'kazar-ai-item';
                        // log simple "click" quand l'utilisateur appuie sur la carte (hors boutons)
                        card.addEventListener('click', (e)=>{
                            const isButton = e.target.closest('button');
                            if(!isButton){ logAiInteraction('click', p.id); }
                        });
                        card.innerHTML = `
                            <div class="d-flex align-items-center gap-2">
                                <img src="${p.image ? (p.image.startsWith('http')?p.image:'/'+p.image) : '/images/produit.jpg'}" style="width:40px;height:40px;object-fit:cover;border-radius:6px;" onerror="this.src='/images/produit.jpg'">
                                <div class="flex-grow-1">
                                    <div class="small fw-bold">${p.name}</div>
                                    <div class="small text-muted">${new Intl.NumberFormat('fr-FR').format(p.price)} FCFA</div>
                                </div>
                                <div class="btn-group btn-group-sm me-1" ${cartIds.has(p.id)?'':'style="display:none;"'}>
                                  <button class="btn btn-outline-secondary" data-minus="${p.id}">−</button>
                                  <button class="btn btn-outline-secondary" data-plus="${p.id}">+</button>
                                </div>
                                <button class="btn btn-sm btn-outline-danger me-1" data-remove="${p.id}" ${cartIds.has(p.id)?'':'style="display:none;"'}>Retirer</button>
                                <button class="btn btn-sm orange-bg text-white" data-id="${p.id}" ${cartIds.has(p.id)?'style="display:none;"':''}>Ajouter</button>
                            </div>`;
                        wrap.appendChild(card);
                    });
                    thread.appendChild(wrap);
                    thread.scrollTop = thread.scrollHeight;
                    wrap.querySelectorAll('button[data-id]').forEach(btn=>{
                        btn.addEventListener('click', ()=> aiAddToCart(parseInt(btn.getAttribute('data-id'),10)) );
                    });
                    wrap.querySelectorAll('button[data-plus]').forEach(btn=>{
                        btn.addEventListener('click', ()=> { logAiInteraction('click', parseInt(btn.getAttribute('data-plus'),10)); aiChangeQty(parseInt(btn.getAttribute('data-plus'),10), +1); } );
                    });
                    wrap.querySelectorAll('button[data-minus]').forEach(btn=>{
                        btn.addEventListener('click', ()=> { logAiInteraction('click', parseInt(btn.getAttribute('data-minus'),10)); aiChangeQty(parseInt(btn.getAttribute('data-minus'),10), -1); } );
                    });
                    wrap.querySelectorAll('button[data-remove]').forEach(btn=>{
                        btn.addEventListener('click', ()=> { logAiInteraction('click', parseInt(btn.getAttribute('data-remove'),10)); aiRemoveFromCart(parseInt(btn.getAttribute('data-remove'),10)); } );
                    });
                }
            }else{
                appendBubble(data.message || 'Erreur', 'ai');
            }
        }catch(e){ appendBubble('Erreur de connexion.', 'ai'); }
    }
    sendBtn.addEventListener('click', ask);
    input.addEventListener('keydown', e=>{ if(e.key==='Enter') ask(); });
});
// end chat script
</script>

<style>
.kazar-ai-wrap { left:20px; bottom:30px; z-index:9999999999!important; }
.kazar-ai-chat { width: 350px; }
.kazar-ai-thread { height: 350px; overflow:auto; }
.kazar-ai-fab { width:60px; height:60px; animation: kazarPulse 2.4s infinite; transform-origin:center; }
.kazar-ai-fab.active { animation: none; }
.kazar-ai-fab:hover { animation: kazarBounce 0.8s; }
.kazar-ai-bubble {
    display: inline-block;
    max-width: 90%;
    padding: 6px 10px;
    border-radius: 10px;
    white-space: pre-wrap; /* permet les retours à la ligne */
}
.kazar-ai-bubble.me { background: var(--bs-primary); color: #fff; }
.kazar-ai-bubble.ai { background: #f8f9fa; color: #212529; }
.kazar-ai-bubble.ai a.btn { display: inline-block; margin-top: 8px; }
.kazar-ai-item { background:#f8f9fa; border:1px solid #e9ecef; border-radius:8px; padding:6px 8px; margin-bottom:6px; }

@keyframes kazarPulse {
  0% { box-shadow: 0 0 0 0 rgba(255,140,0,.6); }
  70% { box-shadow: 0 0 0 20px rgba(255,140,0,0); }
  100% { box-shadow: 0 0 0 0 rgba(255,140,0,0); }
}
@keyframes kazarBounce {
  0%,100% { transform: translateY(0); }
  30% { transform: translateY(-4px); }
  60% { transform: translateY(0); }
}

@media (max-width: 576px) {
  .kazar-ai-wrap { left:10px; bottom:15vh; }
  .kazar-ai-chat { width: 90%; max-width:100%; margin:0 auto; }
  .kazar-ai-thread { height: 50vh; }
}
</style>
@endif


