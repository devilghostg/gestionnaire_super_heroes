class Hero3DViewer {
    constructor(containerId) {
        this.container = document.getElementById(containerId);
        this.scene = new THREE.Scene();
        this.camera = new THREE.PerspectiveCamera(75, this.container.clientWidth / this.container.clientHeight, 0.1, 1000);
        this.renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
        this.controls = null;
        this.mixer = null;
        this.clock = new THREE.Clock();
        this.model = null;

        this.init();
    }

    init() {
        // Configuration du renderer
        this.renderer.setSize(this.container.clientWidth, this.container.clientHeight);
        this.renderer.setPixelRatio(window.devicePixelRatio);
        this.renderer.setClearColor(0x000000, 0);
        this.container.appendChild(this.renderer.domElement);

        // Configuration de la caméra
        this.camera.position.z = 3;
        this.camera.position.y = 1;

        // Ajout des contrôles
        this.controls = new THREE.OrbitControls(this.camera, this.renderer.domElement);
        this.controls.enableDamping = true;
        this.controls.dampingFactor = 0.05;
        this.controls.screenSpacePanning = false;
        this.controls.minDistance = 1;
        this.controls.maxDistance = 5;
        this.controls.maxPolarAngle = Math.PI / 2;

        // Éclairage
        const ambientLight = new THREE.AmbientLight(0xffffff, 0.5);
        this.scene.add(ambientLight);

        const directionalLight = new THREE.DirectionalLight(0xffffff, 1);
        directionalLight.position.set(5, 5, 5);
        this.scene.add(directionalLight);

        // Gestion du redimensionnement
        window.addEventListener('resize', () => this.onWindowResize(), false);

        // Démarrage de l'animation
        this.animate();
    }

    loadModel(modelPath) {
        const loader = new THREE.GLTFLoader();
        
        // Afficher un modèle par défaut en attendant le chargement
        this.loadDefaultModel();

        loader.load(modelPath, (gltf) => {
            if (this.model) {
                this.scene.remove(this.model);
            }

            this.model = gltf.scene;
            this.model.scale.set(1, 1, 1);
            this.scene.add(this.model);

            // Centre le modèle
            const box = new THREE.Box3().setFromObject(this.model);
            const center = box.getCenter(new THREE.Vector3());
            this.model.position.sub(center);

            // Animation si disponible
            if (gltf.animations && gltf.animations.length) {
                this.mixer = new THREE.AnimationMixer(this.model);
                const action = this.mixer.clipAction(gltf.animations[0]);
                action.play();
            }
        }, undefined, (error) => {
            console.error('Erreur lors du chargement du modèle:', error);
            this.loadDefaultModel();
        });
    }

    loadDefaultModel() {
        // Création d'un modèle par défaut (une sphère colorée)
        const geometry = new THREE.SphereGeometry(0.5, 32, 32);
        const material = new THREE.MeshPhongMaterial({
            color: 0x00ff00,
            emissive: 0x072534,
            side: THREE.DoubleSide,
            flatShading: true
        });
        const sphere = new THREE.Mesh(geometry, material);
        
        if (this.model) {
            this.scene.remove(this.model);
        }
        
        this.model = sphere;
        this.scene.add(sphere);
    }

    onWindowResize() {
        this.camera.aspect = this.container.clientWidth / this.container.clientHeight;
        this.camera.updateProjectionMatrix();
        this.renderer.setSize(this.container.clientWidth, this.container.clientHeight);
    }

    animate() {
        requestAnimationFrame(() => this.animate());

        // Met à jour les contrôles
        this.controls.update();

        // Met à jour les animations
        if (this.mixer) {
            this.mixer.update(this.clock.getDelta());
        }

        // Fait tourner le modèle par défaut
        if (this.model && !this.mixer) {
            this.model.rotation.y += 0.01;
        }

        this.renderer.render(this.scene, this.camera);
    }
}
