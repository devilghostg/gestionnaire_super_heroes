class Hero3DViewer {
    constructor(containerId, modelPath) {
        console.log('Hero3DViewer constructor called with:', { containerId, modelPath });
        this.container = document.getElementById(containerId);
        this.modelPath = modelPath;
        this.scene = new THREE.Scene();
        this.camera = new THREE.PerspectiveCamera(75, this.container.clientWidth / this.container.clientHeight, 0.1, 1000);
        this.renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
        this.controls = null;
        this.mixer = null;
        this.clock = new THREE.Clock();
        this.model = null;

        console.log('Starting Hero3DViewer initialization...');
        this.init();
    }

    init() {
        try {
            console.log('Initializing viewer...');
            // Configuration du renderer
            this.renderer.setSize(this.container.clientWidth, this.container.clientHeight);
            this.renderer.setClearColor(0x000000, 0);
            this.renderer.outputColorSpace = THREE.SRGBColorSpace;
            this.container.appendChild(this.renderer.domElement);

            // Configuration de la caméra
            this.camera.position.set(0, 1, 5);

            // Configuration des contrôles
            this.controls = new THREE.OrbitControls(this.camera, this.renderer.domElement);
            this.controls.enableDamping = true;
            this.controls.dampingFactor = 0.05;

            // Ajout de l'éclairage
            const ambientLight = new THREE.AmbientLight(0xffffff, 1);
            this.scene.add(ambientLight);

            const directionalLight = new THREE.DirectionalLight(0xffffff, 2);
            directionalLight.position.set(2, 2, 2);
            this.scene.add(directionalLight);

            // Chargement du modèle
            console.log('Loading model from:', this.modelPath);
            const loader = new THREE.GLTFLoader();
            
            loader.load(
                this.modelPath,
                (gltf) => {
                    console.log('Model loaded successfully:', gltf);
                    this.model = gltf.scene;
                    
                    // Centrer et redimensionner le modèle
                    const box = new THREE.Box3().setFromObject(this.model);
                    const center = box.getCenter(new THREE.Vector3());
                    const size = box.getSize(new THREE.Vector3());
                    
                    console.log('Model size:', size);
                    const maxDim = Math.max(size.x, size.y, size.z);
                    const scale = 2 / maxDim;
                    this.model.scale.setScalar(scale);
                    
                    this.model.position.sub(center.multiplyScalar(scale));
                    
                    this.scene.add(this.model);

                    // Ajuster la caméra pour voir tout le modèle
                    const distance = maxDim * 2;
                    this.camera.position.set(distance, distance / 2, distance);
                    this.camera.lookAt(0, 0, 0);
                    this.controls.update();

                    // Configuration des animations si disponibles
                    if (gltf.animations && gltf.animations.length) {
                        console.log('Model has animations:', gltf.animations.length);
                        this.mixer = new THREE.AnimationMixer(this.model);
                        const action = this.mixer.clipAction(gltf.animations[0]);
                        action.play();
                    }

                    this.animate();
                },
                (progress) => {
                    const percent = (progress.loaded / progress.total * 100).toFixed(2);
                    console.log(`Loading progress: ${percent}%`);
                },
                (error) => {
                    console.error('Error loading model:', error);
                    console.error('Error details:', {
                        message: error.message,
                        stack: error.stack
                    });
                }
            );

            // Gestion du redimensionnement
            window.addEventListener('resize', () => this.onWindowResize(), false);

        } catch (error) {
            console.error('Error in init:', error);
            console.error('Error details:', {
                message: error.message,
                stack: error.stack
            });
        }
    }

    animate() {
        requestAnimationFrame(() => this.animate());

        try {
            // Mettre à jour les contrôles
            if (this.controls) {
                this.controls.update();
            }

            // Mettre à jour les animations
            if (this.mixer) {
                this.mixer.update(this.clock.getDelta());
            }

            // Rendu de la scène
            this.renderer.render(this.scene, this.camera);
        } catch (error) {
            console.error('Error in animate:', error);
            console.error('Error details:', {
                message: error.message,
                stack: error.stack
            });
        }
    }

    onWindowResize() {
        try {
            this.camera.aspect = this.container.clientWidth / this.container.clientHeight;
            this.camera.updateProjectionMatrix();
            this.renderer.setSize(this.container.clientWidth, this.container.clientHeight);
        } catch (error) {
            console.error('Error in onWindowResize:', error);
            console.error('Error details:', {
                message: error.message,
                stack: error.stack
            });
        }
    }
}
