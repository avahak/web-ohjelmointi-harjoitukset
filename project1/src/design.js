import * as THREE from 'three';
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