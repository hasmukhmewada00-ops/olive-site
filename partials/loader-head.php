<?php /* Loader: decide before first paint. Skipped for repeat views in this session, reduced motion, or no JS. */ ?>
<script>
(function(){try{var d=document.documentElement;
if(window.matchMedia&&matchMedia('(prefers-reduced-motion: reduce)').matches)return;
if(sessionStorage.getItem('olv_loader'))return;
sessionStorage.setItem('olv_loader','1');d.classList.add('show-loader');
setTimeout(function(){d.classList.remove('show-loader');},2600);
}catch(e){}})();
</script>
<style>
.loader{display:none}
.show-loader .loader{display:grid;place-items:center;position:fixed;inset:0;z-index:999;background:#2B3526;color:#F8F5F0;animation:olvOut .45s ease 1.6s forwards}
.show-loader body{overflow:hidden}
.loader__svg{width:min(140px,34vw);height:auto;overflow:visible;margin-top:60px}
.loader__dome{transform-box:view-box;transform-origin:315px 300px;animation:olvLift .8s cubic-bezier(.3,.7,.2,1) .3s forwards}
.loader__steam path{stroke-dasharray:420;stroke-dashoffset:420;opacity:0;animation:olvSteam 1.1s ease-out forwards}
.loader__steam path:nth-child(1){animation-delay:.75s}
.loader__steam path:nth-child(2){animation-delay:.6s}
.loader__steam path:nth-child(3){animation-delay:.9s}
@keyframes olvLift{60%{opacity:1}to{transform:translate(70px,-300px) rotate(16deg);opacity:.9}}
@keyframes olvSteam{0%{stroke-dashoffset:420;opacity:0;transform:translateY(0)}35%{opacity:.9}100%{stroke-dashoffset:0;opacity:0;transform:translateY(-70px)}}
@keyframes olvOut{to{opacity:0;visibility:hidden}}
</style>
