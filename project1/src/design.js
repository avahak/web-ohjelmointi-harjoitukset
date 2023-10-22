import * as THREE from 'three';
import { OBJLoader } from 'three/addons/loaders/OBJLoader.js';
import { MTLLoader } from 'three/addons/loaders/MTLLoader.js';

function getRandomColor(r, g, b) {
	// Define a maximum variation you want from the input color
	const maxVariation = 0.5;
  
	// Calculate random variations for each color component
	const randomR = r + (Math.random() - 0.5) * maxVariation;
	const randomG = g + (Math.random() - 0.5) * maxVariation;
	const randomB = b + (Math.random() - 0.5) * maxVariation;
  
	// Ensure the random values are within the valid range [0, 1]
	const finalR = Math.min(1, Math.max(0, randomR));
	const finalG = Math.min(1, Math.max(0, randomG));
	const finalB = Math.min(1, Math.max(0, randomB));
  
	// Create a THREE.js material with the random color
	const material = new THREE.MeshBasicMaterial({ color: new THREE.Color(finalR, finalG, finalB), side: THREE.FrontSide, });
  
	return material;
}

// Loads an .obj file and attaches a .mtl file or a material to it, then adds it to scene:
function loadObjMtl(scene, objPath, material) {
	const loader = new OBJLoader();

	if (material instanceof String) {
		// If material is string, treat it as path for .mtl file
		const mtlLoader = new MTLLoader();
	
		mtlLoader.load(material, (materials) => {
			materials.preload();
	
			loader.setMaterials(materials);
			loader.load(objPath, (object) => {
				scene.add(object);
			}, undefined, error => {
				console.error(`Error loading OBJ: ${error}`);
			});
		}, null, error => {
			console.error(`Error loading MTL: ${error}`);
		});
	} else {
		// If material is not string, treat it as material and apply it to the object:
		loader.load(objPath, (object) => {
			object.traverse((child) => {
				console.log(child);
				if (child instanceof THREE.Mesh) 
					child.material = getRandomColor(0.2, 0.2, 0.7);
			});
			scene.add(object);
		}, undefined, error => {
			console.error(`Error loading OBJ: ${error}`);
		});
	}
}

let ar = 1000.0 / 600.0

const scene = new THREE.Scene();
const camera = new THREE.PerspectiveCamera(75, ar, 0.1, 1000);
// const camera = new THREE.OrthographicCamera(-ar, ar, -1.0, 1.0, 0.1, 1000.0);

const renderer = new THREE.WebGLRenderer({ antialias: true });
// renderer.setSize( window.innerWidth, window.innerHeight );
renderer.setSize(1000, 600);
document.getElementById("test").appendChild(renderer.domElement);

const geometry = new THREE.BoxGeometry(0.2, 0.2, 0.2);
const material1 = new THREE.MeshBasicMaterial({ color: 0x00ff00 });
const cube = new THREE.Mesh(geometry, material1);
scene.add(cube);

cube.position.z = 0.5;

camera.position.z = 1.5;

let time = 0.0;
let dTime = 0.001;

function animate() {
	time += dTime;
	requestAnimationFrame(animate);

	cube.rotation.x = time;
	cube.rotation.y = time;

	// camera.position.x = 0.5*Math.cos(time);
	// camera.position.y = 0.5*Math.sin(time);
	camera.rotation.x = 0.1*Math.cos(5*time);
	camera.rotation.y = 0.1*Math.sin(5*time);

	renderer.render(scene, camera);
}


// loadObjMtl(scene, '../resources/models/old_merge_no_cushions.obj', '../resources/models/old_merge_no_cushions.mtl')
loadObjMtl(scene, '../resources/models/old_merge_no_cushions_tri.obj', new THREE.MeshBasicMaterial({ color: 0x5522ff }))
loadObjMtl(scene, '../resources/models/cushions.obj', new THREE.MeshBasicMaterial({ color: 0xffaa55 }))

animate();

let isDragging = false;
let lastX, lastY;

function handleMouseDown(event) {
	if (event.button === 0) { // Left mouse button
    	isDragging = true;
    	lastX = event.clientX;
    	lastY = event.clientY;
  	}
}

function handleMouseMove(event) {
  	if (isDragging) {
	    const newX = event.clientX;
	    const newY = event.clientY;
	    let dx = newX - lastX;
	    let dy = newY - lastY;
	    lastX = newX;
	    lastY = newY;

	    camera.position.x -= 0.01*dx;
		camera.position.y += 0.01*dy;
  	}
}

function handleMouseUp(event) {
  	if (event.button === 0) {
	    isDragging = false;
  	}
}

// Attach event listeners to the document
document.addEventListener('mousedown', handleMouseDown);
document.addEventListener('mousemove', handleMouseMove);
document.addEventListener('mouseup', handleMouseUp);

document.addEventListener('contextmenu', (event) => {
	event.preventDefault(); // Disable the default context menu
	
	// Handle your custom logic for right-click here
	dTime = (dTime) ? 0.0 : 0.001;
	console.log(event);
});

document.addEventListener('wheel', (event) => {
	event.preventDefault(); // Disable the default scroll behavior

	console.log(event);
	camera.position.z *= Math.exp(0.001*event.deltaY);
}, { passive: false });
  