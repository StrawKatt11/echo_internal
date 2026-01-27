extends CharacterBody2D

@export var SPEED = 300.0
const JUMP_VELOCITY = 400.0
@onready var startposition = global_position

@onready var skin_manager = get_node("/root/Skins")
@onready var tree: AnimationTree = $AnimationTree
@onready var sprite: Sprite2D = $Sprite2D
@onready var animate: AnimationPlayer = $AnimationPlayer

func _ready():
	tree.active = true
	animate.play()

func _physics_process(delta: float):

	if not is_on_floor():
		velocity += (get_gravity() * -1)  * delta

	if Input.is_action_just_pressed("jump") and is_on_floor():
		velocity.y = JUMP_VELOCITY

	var direction := Input.get_axis("right", "left")
	if direction:
		velocity.x = direction * SPEED
	else:
		velocity.x = move_toward(velocity.x, 0, SPEED)

	move_and_slide()
	update_animation()
	update_facing_direction()
	skin_change()

func update_animation():
	tree.set("parameters/move/blend_position", velocity.x)

func die():
	global_position = startposition
func update_facing_direction():
	if velocity.x > 0:
		sprite.flip_h=true
	elif velocity.x < 0:
		sprite.flip_h=false

func skin_change():
	modulate = skin_manager.skins[skin_manager.selected_skin_index-1]
