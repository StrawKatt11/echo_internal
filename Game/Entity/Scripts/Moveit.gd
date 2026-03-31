extends CharacterBody2D

#region variables

@export var SPEED = 300.0
var startposition = position

@onready var skin_manager = get_node("/root/Skins")
@onready var tree: AnimationTree = $AnimationTree
@onready var sprite: Sprite2D = $Sprite2D
@onready var state_machine: CharacterStateMachine = $CharacterStateMachine
@onready var animate: AnimationPlayer = $AnimationPlayer
@onready var pos = global_position

#endregion

#region main

func _ready():
	tree.active = true
	animate.play()

func _physics_process(delta: float):

	if not is_on_floor():
		velocity += get_gravity() * delta

	var direction := Input.get_axis("left", "right")
	if direction && state_machine.check_if_can_move():
		velocity.x = direction * SPEED
	else:
		velocity.x = move_toward(velocity.x, 0, SPEED)

	move_and_slide()
	update_animation()
	update_facing_direction()
	skin_change()

#endregion

#region functions

func update_animation():
	tree.set("parameters/move/blend_position", velocity.x)

func die():
	global_position = pos
func update_facing_direction():
	if velocity.x > 0:
		sprite.flip_h=false
	elif velocity.x < 0:
		sprite.flip_h=true

func skin_change():
	modulate = skin_manager.skins[skin_manager.selected_skin_index-1]

#endregion
