
document.addEventListener('DOMContentLoaded', () => {
  const scene = document.querySelector('[data-rally-scene]');
  const canvas = scene?.querySelector('canvas');
  if (!canvas) return;

  const ctx = canvas.getContext('2d');
  if (!ctx) return;

  const smash = scene.querySelector('[data-rally-smash]');
  const reduced = matchMedia('(prefers-reduced-motion: reduce)');
  const W = 520, H = 490, PI = Math.PI;

  const shots = [
    { a: [.66,.81], b: [.30,.21], s: 1.25, h: 91, by: 'near', name: 'clear' },
    { a: [.30,.21], b: [.75,.78], s: .74, h: 38, by: 'far', name: 'smash' },
    { a: [.75,.78], b: [.45,.30], s: 1.12, h: 104, by: 'near', name: 'defense' },
    { a: [.45,.30], b: [.27,.69], s: .98, h: 43, by: 'far', name: 'drop' },
    { a: [.27,.69], b: [.59,.32], s: .96, h: 62, by: 'near', name: 'net' },
    { a: [.59,.32], b: [.66,.81], s: 1.21, h: 96, by: 'far', name: 'lift' }
  ];

  const actors = {
    near: {x:.66,z:.81},
    far: {x:.30,z:.21}
  };

  let index = 0, elapsed = 0, previous = 0, raf = 0;
  let visible = true;
  let scale = 1, offsetX = 0, offsetY = 0;
  let trail = [], impact = 0;

  const lerp = (a,b,t) => a+(b-a)*t;
  const clamp = (v,a,b) => Math.max(a,Math.min(b,v));
  const proj = (x,z) => ({
    x:W/2+(x-.5)*(204+236*z),
    y:83+350*z
  });

  function segment(x1,y1,x2,y2,color='#f5fffa',width=2) {
    ctx.strokeStyle=color;
    ctx.lineWidth=width;
    ctx.lineCap='round';
    ctx.beginPath();
    ctx.moveTo(x1,y1);
    ctx.lineTo(x2,y2);
    ctx.stroke();
  }

  function courtLine(x1,z1,x2,z2,w=2) {
    const a=proj(x1,z1), b=proj(x2,z2);
    segment(a.x,a.y,b.x,b.y,'rgba(250,255,250,.94)',w);
  }

  function arena(time) {
    const glow=ctx.createRadialGradient(260,230,10,260,230,250);
    glow.addColorStop(0,'rgba(40,205,170,.2)');
    glow.addColorStop(1,'rgba(40,205,170,0)');
    ctx.fillStyle=glow;
    ctx.fillRect(0,0,W,H);

    for(let i=0;i<25;i++) {
      ctx.globalAlpha=.15+.11*Math.sin(time+i);
      ctx.fillStyle=i%3?'#d5f6ff':'#ffb45d';
      ctx.beginPath();
      ctx.arc(20+(i*127.3)%480,22+(i*83.7)%440,1.5,0,2*PI);
      ctx.fill();
    }
    ctx.globalAlpha=1;
  }

  function court() {
    const a=proj(0,0),b=proj(1,0),c=proj(1,1),d=proj(0,1);

    const surface=ctx.createLinearGradient(180,83,350,435);
    surface.addColorStop(0,'#299a70');
    surface.addColorStop(.5,'#197957');
    surface.addColorStop(1,'#105643');

    ctx.save();
    ctx.shadowColor='#0008';
    ctx.shadowBlur=35;
    ctx.shadowOffsetY=20;
    ctx.beginPath();
    ctx.moveTo(a.x,a.y);
    ctx.lineTo(b.x,b.y);
    ctx.lineTo(c.x,c.y);
    ctx.lineTo(d.x,d.y);
    ctx.closePath();
    ctx.fillStyle=surface;
    ctx.fill();
    ctx.restore();

    ctx.save();
    ctx.beginPath();
    ctx.moveTo(a.x,a.y);
    ctx.lineTo(b.x,b.y);
    ctx.lineTo(c.x,c.y);
    ctx.lineTo(d.x,d.y);
    ctx.closePath();
    ctx.clip();

    for(let z=0;z<1;z+=.125) {
      const p=proj(0,z),q=proj(1,z);
      const r=proj(1,z+.125),s=proj(0,z+.125);

      ctx.beginPath();
      ctx.moveTo(p.x,p.y);
      ctx.lineTo(q.x,q.y);
      ctx.lineTo(r.x,r.y);
      ctx.lineTo(s.x,s.y);
      ctx.closePath();
      ctx.fillStyle='rgba(255,255,255,.02)';
      ctx.fill();
    }

    ctx.restore();

    courtLine(0,0,1,0,3);
    courtLine(0,1,1,1,3);
    courtLine(0,0,0,1,3);
    courtLine(1,0,1,1,3);
    courtLine(46/610,0,46/610,1);
    courtLine(564/610,0,564/610,1);

    for(const z of [76/1340,1264/1340,472/1340,868/1340]) {
      courtLine(0,z,1,z);
    }

    courtLine(.5,0,.5,472/1340);
    courtLine(.5,868/1340,.5,1);
  }

  function net() {
    const a=proj(0,.5),b=proj(1,.5),top=a.y-40;

    ctx.fillStyle='rgba(4,22,27,.68)';
    ctx.fillRect(a.x,top,b.x-a.x,40);

    for(let x=a.x;x<b.x;x+=9) {
      segment(x,top,x,a.y,'#d9fff744',.9);
    }

    for(let y=top;y<a.y;y+=8) {
      segment(a.x,y,b.x,y,'#d9fff744',.9);
    }

    segment(a.x,top,b.x,top,'#fff',3);

    for(const p of [a,b]) {
      segment(p.x,p.y-49,p.x,p.y+9,'#f3ffff',5);
    }
  }

  function player(which,time,swing) {
    const near=which==='near';
    const p=actors[which],at=proj(p.x,p.z);

    ctx.save();
    ctx.translate(at.x,at.y);
    ctx.scale(near?1.02:.70,near?1.02:.70);

    ctx.fillStyle='#0006';
    ctx.beginPath();
    ctx.ellipse(0,6,37,12,0,0,2*PI);
    ctx.fill();

    const step=Math.sin(time*11+(near?0:2))*3;
    ctx.translate(0,-Math.abs(Math.sin(time*10))*2);

    segment(-13,-43,-22-step,-18,'#24394f',12);
    segment(-22-step,-18,-27-step,-3,'#24394f',11);
    segment(13,-43,20+step,-20,'#24394f',12);
    segment(20+step,-20,24+step,-3,'#24394f',11);
    segment(-35-step,-3,-16-step,-3,'#fff',9);
    segment(15+step,-3,34+step,-3,'#fff',9);

    ctx.fillStyle='#162b44';
    ctx.fillRect(-24,-50,48,20);

    segment(-22,-83,-36-swing*8,-51-swing*8,'#daa27c',10);

    const jersey=ctx.createLinearGradient(-24,-88,24,-48);
    jersey.addColorStop(0,near?'#ffb35b':'#43bcff');
    jersey.addColorStop(1,near?'#c7661f':'#1664a7');

    ctx.fillStyle=jersey;
    ctx.beginPath();
    ctx.moveTo(-23,-91);
    ctx.quadraticCurveTo(0,-102,23,-91);
    ctx.lineTo(26,-54);
    ctx.quadraticCurveTo(0,-45,-26,-54);
    ctx.closePath();
    ctx.fill();

    segment(-15,-83,-15,-60,'#ffffffa0',2);
    segment(-5,-98,5,-98,'#cc916e',12);

    ctx.fillStyle='#e7b28b';
    ctx.beginPath();
    ctx.ellipse(0,-114,15,18,0,0,2*PI);
    ctx.fill();

    ctx.fillStyle='#172a35';
    ctx.beginPath();
    ctx.ellipse(0,-126,15,8,0,PI,2*PI);
    ctx.fill();

    ctx.save();
    if(!near)ctx.scale(-1,1);

    const elbow={
      x:36+swing*11,
      y:-67-swing*12
    };

    const wrist={
      x:39+swing*27,
      y:-49-swing*45
    };

    segment(22,-83,elbow.x,elbow.y,'#e8ae87',11);
    segment(elbow.x,elbow.y,wrist.x,wrist.y,'#e8ae87',11);

    const rx=wrist.x+20+swing*4;
    const ry=wrist.y-40-swing*10;

    segment(wrist.x,wrist.y,rx,ry,'#e6f7ff',4);

    ctx.save();
    ctx.translate(rx+6,ry-12);
    ctx.rotate(.2+swing*.5);
    ctx.strokeStyle='#f8ffff';
    ctx.lineWidth=3;

    ctx.beginPath();
    ctx.ellipse(0,0,13,23,0,0,2*PI);
    ctx.stroke();

    for(let k=-8;k<=8;k+=5) {
      segment(k,-19,k,19,'#e3ffff70',.7);
    }

    for(let k=-12;k<=12;k+=6) {
      segment(-11,k,11,k,'#e3ffff70',.7);
    }

    ctx.restore();
    ctx.restore();
    ctx.restore();
  }

  function flight(shot,t) {
    const a=proj(...shot.a),b=proj(...shot.b);

    return {
      x:lerp(a.x,b.x,t)+Math.sin(PI*t)*10,
      y:lerp(
        a.y-(shot.by==='near'?103:75),
        b.y-(shot.by==='near'?75:103),
        t
      )-shot.h*Math.sin(PI*t)
    };
  }

  function shuttle(point,angle) {
    ctx.save();
    ctx.translate(point.x,point.y);
    ctx.rotate(angle);

    ctx.shadowBlur=13;
    ctx.shadowColor='#fff';
    ctx.fillStyle='#f7ffff';

    ctx.beginPath();
    ctx.moveTo(-16,-11);
    ctx.lineTo(5,-5);
    ctx.lineTo(5,5);
    ctx.lineTo(-16,11);
    ctx.lineTo(-8,0);
    ctx.closePath();
    ctx.fill();

    for(let y=-8;y<=8;y+=4) {
      segment(-14,y,5,y*.3,'#80bed1',.8);
    }

    ctx.fillStyle='#ffbd5f';
    ctx.beginPath();
    ctx.arc(10,0,5,0,2*PI);
    ctx.fill();
    ctx.restore();
  }

  function render(time) {
    ctx.setTransform(1,0,0,1,0,0);
    ctx.clearRect(0,0,canvas.width,canvas.height);

    const dpr=canvas.width/Math.max(1,canvas.clientWidth);
    ctx.setTransform(dpr,0,0,dpr,0,0);
    ctx.translate(offsetX,offsetY);
    ctx.scale(scale,scale);

    const shot=shots[index];
    const t=clamp(elapsed/shot.s,0,1);
    const swing=Math.max(0,1-t/.28)**1.45;

    arena(time);
    court();
    player('far',time,shot.by==='far'?swing:0);
    net();
    player('near',time,shot.by==='near'?swing:0);

    for(let i=1;i<trail.length;i++) {
      const alpha=i/trail.length;
      segment(
        trail[i-1].x,trail[i-1].y,
        trail[i].x,trail[i].y,
        `rgba(255,237,195,${alpha*.7})`,
        .8+alpha*3.8
      );
    }

    if(impact<.28) {
      const p=flight(shot,0);
      ctx.strokeStyle=`rgba(255,193,99,${1-impact/.28})`;
      ctx.lineWidth=2;
      ctx.beginPath();
      ctx.arc(p.x,p.y,10+impact*85,0,2*PI);
      ctx.stroke();
    }

    const p=flight(shot,t);
    const p2=flight(shot,Math.min(1,t+.01));
    shuttle(p,Math.atan2(p2.y-p.y,p2.x-p.x));
  }

  function announce(shot) {
    if(shot.name!=='smash'||!smash)return;

    const p=flight(shot,0);
    smash.style.left=`${(offsetX+p.x*scale)/canvas.clientWidth*100}%`;
    smash.style.top=`${(offsetY+p.y*scale)/canvas.clientHeight*100}%`;

    smash.classList.remove('is-active');
    void smash.offsetWidth;
    smash.classList.add('is-active');
  }

  function resize() {
    const w=canvas.clientWidth,h=canvas.clientHeight;
    if(!w||!h)return;

    const dpr=Math.min(devicePixelRatio||1,2);
    canvas.width=Math.round(w*dpr);
    canvas.height=Math.round(h*dpr);

    scale=Math.min(w/W,h/H);
    offsetX=(w-W*scale)/2;
    offsetY=(h-H*scale)/2;
    render(0);
  }

  function tick(stamp) {
    const dt=previous?Math.min(.045,(stamp-previous)/1000):0;
    previous=stamp;
    elapsed+=dt;
    impact+=dt;

    let shot=shots[index];

    if(elapsed>=shot.s) {
      elapsed-=shot.s;
      index=(index+1)%shots.length;
      shot=shots[index];
      trail=[];
      impact=0;
      announce(shot);
    }

    const next=shots[(index+1)%shots.length];

    for(const side of ['near','far']) {
      const target=shot.by===side?next.a:shot.b;

      actors[side].x=lerp(
        actors[side].x,target[0],
        Math.min(1,dt*3.2)
      );

      actors[side].z=lerp(
        actors[side].z,
        clamp(
          target[1],
          side==='far'?.14:.63,
          side==='far'?.40:.88
        ),
        Math.min(1,dt*2)
      );
    }

    trail.push(flight(shot,clamp(elapsed/shot.s,0,1)));

    if(trail.length>12)trail.shift();

    render(stamp/1000);
    raf=requestAnimationFrame(tick);
  }

  function playback() {
    cancelAnimationFrame(raf);
    raf=0;
    previous=0;

    if(visible&&!document.hidden&&!reduced.matches) {
      raf=requestAnimationFrame(tick);
    } else {
      render(0);
    }
  }

  if('ResizeObserver' in window) {
    new ResizeObserver(resize).observe(scene);
  } else {
    window.addEventListener('resize',resize);
  }

  if('IntersectionObserver' in window) {
    new IntersectionObserver(([entry])=>{
      visible=entry.isIntersecting;
      playback();
    }).observe(scene);
  }

  document.addEventListener('visibilitychange',playback);
  reduced.addEventListener?.('change',playback);

  resize();
  playback();
});
