(function(){
    const canvas = document.getElementById('bg-canvas');
    if (!canvas || !window.THREE) return;
    const W = window.innerWidth, H = window.innerHeight;
    const scene = new THREE.Scene();
    const camera = new THREE.PerspectiveCamera(60, W/H, 0.1, 1000);
    camera.position.z = 14;
    const renderer = new THREE.WebGLRenderer({ canvas, alpha: true, antialias: true });
    renderer.setSize(W, H);
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
    renderer.setClearColor(0x000000, 0);

    const COUNT = 110;
    const geo = new THREE.BufferGeometry();
    const pos = new Float32Array(COUNT * 3);
    const vel = [];
    for (let i = 0; i < COUNT; i++) {
        pos[i*3]   = (Math.random()-.5)*26;
        pos[i*3+1] = (Math.random()-.5)*26;
        pos[i*3+2] = (Math.random()-.5)*4;
        vel.push({ x:(Math.random()-.5)*0.011, y:(Math.random()-.5)*0.011 });
    }
    geo.setAttribute('position', new THREE.BufferAttribute(pos, 3));

    const pColor = (canvas.dataset.color && parseInt(canvas.dataset.color, 16)) || 0x38bdf8;
    const lColor = (canvas.dataset.linecolor && parseInt(canvas.dataset.linecolor, 16)) || 0x7dd3fc;

    scene.add(new THREE.Points(geo, new THREE.PointsMaterial({
        color: pColor, size: 0.09, transparent: true, opacity: 0.5, sizeAttenuation: true
    })));

    const MAX_L = 300, lPos = new Float32Array(MAX_L * 6);
    const lGeo  = new THREE.BufferGeometry();
    lGeo.setAttribute('position', new THREE.BufferAttribute(lPos, 3));
    lGeo.setDrawRange(0, 0);
    scene.add(new THREE.LineSegments(lGeo, new THREE.LineBasicMaterial({
        color: lColor, transparent: true, opacity: 0.12
    })));

    (function animate(){
        requestAnimationFrame(animate);
        for (let i=0;i<COUNT;i++){
            pos[i*3]  +=vel[i].x; pos[i*3+1]+=vel[i].y;
            if(Math.abs(pos[i*3])>13)  vel[i].x*=-1;
            if(Math.abs(pos[i*3+1])>13)vel[i].y*=-1;
        }
        geo.attributes.position.needsUpdate=true;
        let li=0; const th=5;
        for(let i=0;i<COUNT&&li<MAX_L;i++){
            for(let j=i+1;j<COUNT&&li<MAX_L;j++){
                const dx=pos[i*3]-pos[j*3], dy=pos[i*3+1]-pos[j*3+1];
                if(Math.sqrt(dx*dx+dy*dy)<th){
                    lPos[li*6]=pos[i*3]; lPos[li*6+1]=pos[i*3+1]; lPos[li*6+2]=0;
                    lPos[li*6+3]=pos[j*3]; lPos[li*6+4]=pos[j*3+1]; lPos[li*6+5]=0;
                    li++;
                }
            }
        }
        lGeo.setDrawRange(0,li*2);
        lGeo.attributes.position.needsUpdate=true;
        renderer.render(scene,camera);
    })();

    window.addEventListener('resize',()=>{
        camera.aspect=window.innerWidth/window.innerHeight;
        camera.updateProjectionMatrix();
        renderer.setSize(window.innerWidth,window.innerHeight);
    });
})();
