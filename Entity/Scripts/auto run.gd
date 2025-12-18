extends CharacterBody2D


@export var SPEED = 300.0

@onready var tree: AnimationTree = $AnimationTree
@onready var sprite: Sprite2D = $Sprite2D
@onready var state_machine: CharacterStateMachine = $CharacterStateMachine
@onready var animate: AnimationPlayer = $AnimationPlayer
func _ready():
	tree.active = true
	animate.play()

func _physics_process(delta: float):

	if not is_on_floor():
		velocity += get_gravity() * delta

	velocity.x = 1.0 * SPEED

	move_and_slide()
	update_animation()
	update_facing_direction()

func update_animation():
	tree.set("parameters/move/blend_position", velocity.x)

func die():
	global_position = Vector2(2, -38)
func update_facing_direction():
	if velocity.x > 0:
		sprite.flip_h=false
	elif velocity.x < 0:
		sprite.flip_h=true
