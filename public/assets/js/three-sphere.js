// Three.js metallic sphere animation
let scene, camera, renderer, sphere, animationId;

function initThreeSphere() {
    // Skip on mobile devices for performance
    if (window.innerWidth <= 768 || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        return;
    }

    // Skip if user disabled animations
    if (localStorage.getItem('disable-animations') === 'true') {
        return;
    }

    try {
        // Create scene
        scene = new THREE.Scene();

        // Create camera
        camera = new THREE.PerspectiveCamera(75, 1, 0.1, 1000);
        camera.position.z = 3;

        // Create renderer
        renderer = new THREE.WebGLRenderer({ 
            alpha: true, 
            antialias: true,
            powerPreference: "high-performance"
        });
        renderer.setSize(200, 200);
        renderer.setClearColor(0x000000, 0);
        renderer.shadowMap.enabled = true;
        renderer.shadowMap.type = THREE.PCFSoftShadowMap;

        // Add renderer to DOM
        const container = document.getElementById('three-canvas');
        if (container) {
            container.appendChild(renderer.domElement);
        } else {
            return; // Exit if container doesn't exist
        }

        // Create metallic sphere geometry
        const geometry = new THREE.SphereGeometry(1, 32, 32);

        // Create metallic material with PBR
        const material = new THREE.MeshStandardMaterial({
            color: 0x4a90e2,
            metalness: 0.9,
            roughness: 0.1,
            envMapIntensity: 1,
        });

        // Create sphere mesh
        sphere = new THREE.Mesh(geometry, material);
        sphere.castShadow = true;
        sphere.receiveShadow = true;
        scene.add(sphere);

        // Add lights
        const ambientLight = new THREE.AmbientLight(0x404040, 0.4);
        scene.add(ambientLight);

        const directionalLight = new THREE.DirectionalLight(0xffffff, 1);
        directionalLight.position.set(5, 5, 5);
        directionalLight.castShadow = true;
        directionalLight.shadow.mapSize.width = 1024;
        directionalLight.shadow.mapSize.height = 1024;
        scene.add(directionalLight);

        const pointLight = new THREE.PointLight(0x667eea, 0.5, 100);
        pointLight.position.set(-5, -5, -5);
        scene.add(pointLight);

        const pointLight2 = new THREE.PointLight(0x764ba2, 0.3, 100);
        pointLight2.position.set(5, -5, 5);
        scene.add(pointLight2);

        // Add environment map for reflections
        const cubeTextureLoader = new THREE.CubeTextureLoader();
        const urls = [
            'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMSIgaGVpZ2h0PSIxIiB2aWV3Qm94PSIwIDAgMSAxIiBmaWxsPSJub25lIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciPjxyZWN0IHdpZHRoPSIxIiBoZWlnaHQ9IjEiIGZpbGw9IiM2NjdlZWEiLz48L3N2Zz4=',
            'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMSIgaGVpZ2h0PSIxIiB2aWV3Qm94PSIwIDAgMSAxIiBmaWxsPSJub25lIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciPjxyZWN0IHdpZHRoPSIxIiBoZWlnaHQ9IjEiIGZpbGw9IiM3NjRiYTIiLz48L3N2Zz4=',
            'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMSIgaGVpZ2h0PSIxIiB2aWV3Qm94PSIwIDAgMSAxIiBmaWxsPSJub25lIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciPjxyZWN0IHdpZHRoPSIxIiBoZWlnaHQ9IjEiIGZpbGw9IiNmMDkzZmIiLz48L3N2Zz4=',
            'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMSIgaGVpZ2h0PSIxIiB2aWV3Qm94PSIwIDAgMSAxIiBmaWxsPSJub25lIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciPjxyZWN0IHdpZHRoPSIxIiBoZWlnaHQ9IjEiIGZpbGw9IiNmNTU3NmMiLz48L3N2Zz4=',
            'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMSIgaGVpZ2h0PSIxIiB2aWV3Qm94PSIwIDAgMSAxIiBmaWxsPSJub25lIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciPjxyZWN0IHdpZHRoPSIxIiBoZWlnaHQ9IjEiIGZpbGw9IiM0ZmFjZmUiLz48L3N2Zz4=',
            'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMSIgaGVpZ2h0PSIxIiB2aWV3Qm94PSIwIDAgMSAxIiBmaWxsPSJub25lIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciPjxyZWN0IHdpZHRoPSIxIiBoZWlnaHQ9IjEiIGZpbGw9IiMwMGYyZmUiLz48L3N2Zz4='
        ];

        try {
            const envMap = cubeTextureLoader.load(urls);
            scene.environment = envMap;
            material.envMap = envMap;
        } catch (e) {
            console.log('Could not load environment map, continuing without');
        }

        // Start animation loop
        animate();

        // Handle window resize
        window.addEventListener('resize', onWindowResize, false);

        console.log('Three.js metallic sphere initialized successfully');

    } catch (error) {
        console.error('Error initializing Three.js sphere:', error);
        // Clean up any partially created objects
        cleanupThreeSphere();
    }
}

function animate() {
    if (!sphere || !renderer || !scene || !camera) {
        return;
    }

    animationId = requestAnimationFrame(animate);

    // Rotate the sphere
    sphere.rotation.x += 0.005;
    sphere.rotation.y += 0.01;

    // Add floating motion
    sphere.position.y = Math.sin(Date.now() * 0.001) * 0.1;

    // Change color based on time
    const time = Date.now() * 0.0005;
    sphere.material.color.setHSL((time % 1), 0.7, 0.6);

    try {
        renderer.render(scene, camera);
    } catch (error) {
        console.error('Error rendering Three.js scene:', error);
        cleanupThreeSphere();
    }
}

function onWindowResize() {
    if (!camera || !renderer) return;

    // Hide on mobile
    if (window.innerWidth <= 768) {
        const container = document.getElementById('three-canvas');
        if (container) {
            container.style.display = 'none';
        }
        cleanupThreeSphere();
        return;
    }

    // Update camera and renderer for desktop
    camera.aspect = 1;
    camera.updateProjectionMatrix();
    renderer.setSize(200, 200);
}

function cleanupThreeSphere() {
    // Cancel animation
    if (animationId) {
        cancelAnimationFrame(animationId);
        animationId = null;
    }

    // Dispose of Three.js objects
    if (scene) {
        scene.traverse((object) => {
            if (object.geometry) {
                object.geometry.dispose();
            }
            if (object.material) {
                if (object.material.map) object.material.map.dispose();
                if (object.material.envMap) object.material.envMap.dispose();
                object.material.dispose();
            }
        });
        scene.clear();
        scene = null;
    }

    if (renderer) {
        const container = document.getElementById('three-canvas');
        if (container && renderer.domElement && container.contains(renderer.domElement)) {
            container.removeChild(renderer.domElement);
        }
        renderer.dispose();
        renderer = null;
    }

    camera = null;
    sphere = null;
}

// Performance monitoring
let lastTime = performance.now();
let frameCount = 0;
let fps = 0;

function monitorPerformance() {
    frameCount++;
    const currentTime = performance.now();
    
    if (currentTime - lastTime >= 1000) {
        fps = frameCount;
        frameCount = 0;
        lastTime = currentTime;
        
        // If FPS drops too low, disable the sphere
        if (fps < 20 && sphere) {
            console.warn('Low FPS detected, disabling Three.js sphere');
            cleanupThreeSphere();
            localStorage.setItem('disable-3d-sphere', 'true');
        }
    }
}

// Check if sphere should be disabled due to previous performance issues
if (localStorage.getItem('disable-3d-sphere') === 'true') {
    console.log('Three.js sphere disabled due to previous performance issues');
} else {
    // Initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initThreeSphere);
    } else {
        initThreeSphere();
    }
}

// Pause animation when page is not visible
document.addEventListener('visibilitychange', function() {
    if (document.hidden && animationId) {
        cancelAnimationFrame(animationId);
        animationId = null;
    } else if (!document.hidden && sphere && !animationId) {
        animate();
    }
});

// Clean up when page is unloaded
window.addEventListener('beforeunload', cleanupThreeSphere);

// Export functions for global access
window.initThreeSphere = initThreeSphere;
window.cleanupThreeSphere = cleanupThreeSphere;