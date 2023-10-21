// Help me work out a problem with using three.js library. I am developing my own website and running it with xampp on localhost in windows. I want to add three.js code to a page on my project. The base directory of my project is "c:/xampp/htdocs/kurssi" and all my .php and .js files are located at "c:/xampp/htdocs/kurssi/project1/src". I ran "npm install three" at the base directory of my project and this created "c:/xampp/htdocs/kurssi/node_modules" with three inside it, just like it should. 
// The problem came when i try to use three.js in "c:/xampp/htdocs/kurssi/project1/src/design.php" and "design.js" in the same directory. In the design.php file i have `<script type="module" src="./design.js"></script>` and in the design.js file i have `import * as THREE from 'three';`. However, when i run the php script on localhost, the three.js code i have is not working and i get console log `Uncaught TypeError: Failed to resolve module specifier "three". Relative references must start with either "/", "./", or "../".` How can i fix this?

import * as THREE from 'three';
// import * as THREE from "/kurssi/node_modules/three/build/three.module.js"
import { OBJLoader } from 'three/addons/loaders/OBJLoader.js';

let ar = 600.0 / 400.0

const scene = new THREE.Scene();
const camera = new THREE.PerspectiveCamera( 75, ar, 0.1, 1000 );

const renderer = new THREE.WebGLRenderer();
// renderer.setSize( window.innerWidth, window.innerHeight );
renderer.setSize( 600, 400 );
document.getElementById("test").appendChild( renderer.domElement );

const geometry = new THREE.BoxGeometry( 1, 1, 1 );
const material1 = new THREE.MeshBasicMaterial( { color: 0x00ff00 } );
const material2 = new THREE.MeshBasicMaterial( { color: 0xffaa55 } );
const cube = new THREE.Mesh( geometry, material1 );
scene.add( cube );

camera.position.z = 5;

function animate() {
	requestAnimationFrame( animate );

	cube.rotation.x += 0.01;
	cube.rotation.y += 0.01;

	renderer.render( scene, camera );
}

// instantiate a loader
const loader = new OBJLoader();

// load a resource
loader.load(
	// resource URL
	'../resources/models/cushions.obj',
	// called when resource is loaded
	function ( object ) {

        object.traverse((child) => {
            if (child instanceof THREE.Mesh) {
                child.material = material2;
            }
        } );
		scene.add( object );

	},
	// called when loading is in progresses
	function ( xhr ) {

		console.log( ( xhr.loaded / xhr.total * 100 ) + '% loaded' );

	},
	// called when loading has errors
	function ( error ) {

		console.log( 'An error happened', error);

	}
);

animate();