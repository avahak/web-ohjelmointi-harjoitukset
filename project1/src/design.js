import * as THREE from 'three';
import { OBJLoader } from 'three/addons/loaders/OBJLoader.js';
import { MTLLoader } from 'three/addons/loaders/MTLLoader.js';

const jsGlobals = { objects: [], materials: [] };

// Returns a promise that is resolved after loading the .json file
function loadJsonPromise() {
	return new Promise((resolve, reject) => {
		fetch('../resources/models/pooltable.json')
			.then(response => {
				if (!response.ok)
					throw new Error("Network response not ok.");
				return response.json();
			})
			.then(data => {
				jsGlobals.specs = data;
				resolve(data);
			})
			.catch(error => {
				reject(error);
			});
	});
}

// Loads an .obj file and attaches a .mtl file or a material to it.
// Returns a promise that is resolved on load.
function loadObjMtlPromise(name, objPath, material) {
	if (material == null)
		material = new THREE.MeshBasicMaterial({ });
	return new Promise((resolve, reject) => {
		const loader = new OBJLoader();

		if (typeof material == "string") {
			// If material is string, treat it as path for .mtl file
			const mtlLoader = new MTLLoader();
		
			mtlLoader.load(material, (materials) => {
				materials.preload();
		
				loader.setMaterials(materials);
				loader.load(objPath, (object) => {
					jsGlobals.objects[name] = object;
					resolve(object);
				}, undefined, error => {
					reject(error);
				});
			}, null, error => {
				reject(error);
			});
		} else {
			// If material is not string, treat it as material and apply it to the object:
			loader.load(objPath, (object) => {
				object.traverse((child) => {
					if (child instanceof THREE.Mesh) 
						child.material = material;
						// child.material = getRandomColor(0.2, 0.2, 0.7);
				});
				jsGlobals.objects[name] = object;
				resolve(object);
			}, undefined, error => {
				reject(error);
			});
		}
	});
}

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

let ar = 1000.0 / 600.0

const scene = new THREE.Scene();
const camera = new THREE.PerspectiveCamera(20, ar, 0.1, 1000);
// const camera = new THREE.OrthographicCamera(-ar, ar, 1.0, -1.0, 0.1, 1000.0);
camera.position.set(0, 0, 5.5);
camera.lookAt(0.0, 0.0, 0.0);

for (let k = -1; k <= 1; k++) {
	let light = new THREE.PointLight(0xffffff, 5, 10);
	light.position.set(k, 0, 2);
	light.castShadow = true;
	scene.add(light);
}
let light = new THREE.AmbientLight(0xffffff, 0.2, 10);
scene.add(light);

const renderer = new THREE.WebGLRenderer({ alpha: true, antialias: true });
renderer.setClearColor(0x000000, 0);
renderer.shadowMap.enabled = true;
renderer.shadowMap.type = THREE.PCFSoftShadowMap;
// renderer.setSize( window.innerWidth, window.innerHeight );
renderer.setSize(1000, 600);
jsGlobals.element = document.getElementById("three-box");
jsGlobals.element.appendChild(renderer.domElement);

// const geometry = new THREE.BoxGeometry(0.2, 0.2, 0.2);
// const material1 = new THREE.MeshBasicMaterial({ color: 0x00ff00 });
// const cube = new THREE.Mesh(geometry, material1);
// scene.add(cube);
// cube.position.z = 0.5;

let time = 0.0;

function animate() {
	time += 0.001;

	if (jsGlobals.animateCamera) {
		camera.position.set(4*Math.cos(3*time), 4*Math.sin(3*time), 2);
		camera.up.set(0, 0, 4);
		camera.lookAt(0.0, 0.0, 0.0);
	} else {
		camera.position.set(0, 0, 5.5);
		camera.up.set(0, 1, 0);
		camera.lookAt(0.0, 0.0, 0.0);
	}

	renderer.render(scene, camera);

	requestAnimationFrame(animate);
}

let isDragging = false;
let lastX, lastY;

function handleMouseDown(event) {
	if (event.button === 0) { // Left mouse button
    	isDragging = true;
    	lastX = event.clientX;
    	lastY = event.clientY;
		mouseAction( { action: "down", x: lastX, y: lastY } );
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

		// camera.position.x -= 0.01*dx;
		// camera.position.y += 0.01*dy;
		mouseAction( { action: "drag", x: lastX, y: lastY, dx: dx, dy: dy } );
  	}
}

function handleMouseUp(event) {
  	if (event.button === 0) {
	    isDragging = false;
		mouseAction( { action: "up", x: event.clientX, y: event.clientY } );
  	}
}

document.addEventListener('contextmenu', (event) => {
	event.preventDefault(); // Disable the default context menu
	
	// Handle your custom logic for right-click here
	// dTime = (dTime) ? 0.0 : 0.001;

	mouseAction( { action: "contextmenu", x: event.clientX, y: event.clientY } );
});

document.addEventListener('wheel', 
	(event) => {
		event.preventDefault(); // Disable the default scroll behavior

		// console.log(event);
		// camera.position.z *= Math.exp(0.005*event.deltaY);
	
		if (event.deltaY > 0)
			jsGlobals.animateCamera = true;
		else 
			jsGlobals.animateCamera = undefined;
	}, { passive: false });

// Attach event listeners to the document
document.addEventListener('mousedown', handleMouseDown);
document.addEventListener('mousemove', handleMouseMove);
document.addEventListener('mouseup', handleMouseUp);

function findGroupForObject(object) {
	do {
		if (object instanceof THREE.Group)
			return object;
		object = object.parent;
	} while (object.parent);
	return object;
}

function findNameForObject(object) {
	for (const key in jsGlobals.objects) {
		if (jsGlobals.objects[key] == object)
			return key;
	}
	return null;
}

function findObjectNameOnMouse(mouse) {
	const rect = jsGlobals.element.getBoundingClientRect();
	const nMouse = new THREE.Vector2();
	nMouse.x = 2*((mouse.x-rect.left) / rect.width) - 1;
	nMouse.y = -2*((mouse.y-rect.top) / rect.height) + 1;

	const raycaster = new THREE.Raycaster();
	raycaster.setFromCamera(nMouse, camera);
	const intersects = raycaster.intersectObjects(scene.children, true);
	if (intersects.length > 0) {
		// console.log("intersects[0]:", intersects[0].object);
		let x = findGroupForObject(intersects[0].object);
		return findNameForObject(x);
	}
	return null;
}

// Custom mouse left click/drag handler:
function mouseAction(obj) {
	if (obj.action == "down") {
		let y = findObjectNameOnMouse(obj);
		if (y && y.startsWith("ball")) {
			jsGlobals.draggingBall = y.match(/\d+/)[0];
		} 
	} else if (obj.action == "up") {
		jsGlobals.draggingBall = undefined;
	} else if (obj.action == "drag") {
		const rect = jsGlobals.element.getBoundingClientRect();
		const mouse = new THREE.Vector2();
		mouse.x = 2*((obj.x-rect.left) / rect.width) - 1;
		mouse.y = -2*((obj.y-rect.top) / rect.height) + 1;
		const mouse3D = new THREE.Vector3(mouse.x, mouse.y, 0.5);
		let a = mouse3D.unproject(camera);
		const ray = new THREE.Ray(camera.position, a.clone().sub(camera.position).normalize());
		const plane = new THREE.Plane(new THREE.Vector3(0, 0, 1), -jsGlobals.specs.BALL_RADIUS);
		let intersect = new THREE.Vector3();
		ray.intersectPlane(plane, intersect);
		if (intersect) {
			if (jsGlobals.draggingBall) {
				const ball = jsGlobals.objects[`ball${jsGlobals.draggingBall}`];
				ball.position.x = intersect.x;
				ball.position.y = intersect.y;
			}
		}
	} else if (obj.action == "contextmenu") {
		let y = findObjectNameOnMouse(obj);
		if (y && y.startsWith("ball")) {
			jsGlobals.draggingBall = undefined;
			jsGlobals.objects[y].position.copy(jsGlobals.objects[y].defaultPosition);
		}
	}
}
  
function setShadow(object, castShadow, receiveShadow) {
	object.traverse((child) => {
		if (child instanceof THREE.Mesh) {
			child.castShadow = castShadow; 
			child.receiveShadow = receiveShadow;
		}
	});
}

for (let k = 0; k < 16; k++) {
	jsGlobals["materials"][`ball${k}`] = new THREE.MeshStandardMaterial({ color: 0x336699, roughness: 0.2, metalness: 0.2 });
	const textureLoader = new THREE.TextureLoader();
	textureLoader.load(`../resources/models/images/balls/ball${k}.png`, (texture) => {
		jsGlobals["materials"][`ball${k}`].color = undefined;
		jsGlobals["materials"][`ball${k}`].map = texture;
		jsGlobals["materials"][`ball${k}`].needsUpdate = true;
	});
}

const resourcePromises = [
	loadObjMtlPromise("cushions", '../resources/models/cushions.obj', new THREE.MeshStandardMaterial({ color: 0x35557c })),
	loadObjMtlPromise("table", '../resources/models/table.obj', '../resources/models/table.mtl'),
	loadObjMtlPromise("ball", '../resources/models/ball.obj', null),
	loadJsonPromise(),
];

Promise.all(resourcePromises)
	.then(() => {
		let ball = jsGlobals.objects.ball;
		for (let k = 0; k < 16; k++) {
			const cball = ball.clone();
			cball.traverse(child => {
				child.material = jsGlobals.materials[`ball${k}`];
			});
			let r = jsGlobals.specs.BALL_RADIUS;
			cball.scale.set(r, r, r);
			cball.defaultPosition = new THREE.Vector3(-1.0+0.1*k, 0.84, r);
			cball.position.copy(cball.defaultPosition);
			if (INITIAL_VALUES) {
				cball.position.x = INITIAL_VALUES[k][0];
				cball.position.y = INITIAL_VALUES[k][1];
				cball.position.z = INITIAL_VALUES[k][2];
			}
			jsGlobals.objects[`ball${k}`] = cball;
		}

		scene.add(jsGlobals.objects.table);
		scene.add(jsGlobals.objects.cushions);
		setShadow(jsGlobals.objects.table, true, true);
		setShadow(jsGlobals.objects.cushions, true, true);
		for (let k = 0; k < 16; k++) {
			scene.add(jsGlobals.objects[`ball${k}`]);
			setShadow(jsGlobals.objects[`ball${k}`], true, true);
		}

		console.log(jsGlobals);
		animate();
	})
	.catch(error => {
		console.log("Error loading resources: ", error);
	});

document.getElementById('save').addEventListener('click', saveIt);

function saveIt() {
	const currentURL = window.location.origin + window.location.pathname;
	const postData = [];
	for (let k = 0; k < 16; k++) {
		const bp = jsGlobals.objects[`ball${k}`].position;
		postData[k] = [bp.x, bp.y, bp.z];
	}
	const headers = new Headers({
		'Content-Type': 'application/json',
	});
	const requestOptions = {
		method: 'POST',
		headers: headers,
		body: JSON.stringify(postData),
	};
	fetch(currentURL, requestOptions)
		.then(response => {
			if (!response.ok)
				throw new Error('Network response was not ok');
			return response.json();
		})
		.then(data => {
			console.log("saveIt got data back:", data);
			alert(data.message);
		})
		.catch(error => {
			console.error('There was a problem with the fetch operation:', error);
		});
}