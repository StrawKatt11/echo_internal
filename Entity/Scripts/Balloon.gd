extends CharacterBody2D

@export var SPEED = 300.0
var gravity = -100

@onready var skin_manager = get_node("/root/Skins")
@onready var tree: AnimationTree = $AnimationTree
@onready var sprite: Sprite2D = $Sprite2D
@onready var animate: AnimationPlayer = $AnimationPlayer
@onready var layer_1: TileMapLayer = $"../TileMap/Layer1"
@onready var pos = global_position

func _ready():
	tree.active = true
	animate.play()

func _physics_process(delta: float):
	
	if not is_on_floor():
		velocity.y = gravity * 2

	var direction := Input.get_axis("left", "right")
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
	global_position = pos
func update_facing_direction():
	if velocity.x > 0:
		sprite.flip_h=false
	elif velocity.x < 0:
		sprite.flip_h=true


func _on_area_2d_body_entered(body: Node2D) -> void:
	die()

func skin_change():
	modulate = skin_manager.skins[skin_manager.selected_skin_index-1]
