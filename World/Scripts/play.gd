extends Control

var playbutton = false
var quitbutton = false
@onready var skin_manager = get_node("/root/Skins")
@onready var transitions: CanvasLayer = $Transition
@onready var camera_2d: Camera2D = $Camera2D
@onready var button: Button = $Button
@onready var button_2: Button = $Button2
@onready var button_3: Button = $Button3
@onready var button_4: Button = $Button4
@onready var button_7: Button = $Button7
@onready var button_5: Button = $Button5
@onready var button_6: Button = $Button6
@onready var animsprite: AnimatedSprite2D = $AnimatedSprite2D2

func _ready():
	camera_2d.enabled = false

func _process(delta):
	if playbutton == true or quitbutton == true:
		transitions.transition()

func _on_button_pressed() -> void:
	playbutton = true


func _on_button_2_pressed() -> void:
	pass # Replace with function body.


func _on_button_3_pressed() -> void:
	quitbutton = true


func _on_transition_transitioned() -> void:
	if playbutton == true:
		get_tree().change_scene_to_file("res://World/Scenes/Level_1.tscn")
	elif quitbutton == true:
		get_tree().quit()


func _on_button_4_pressed() -> void:
	button.visible = false
	button_2.visible = false
	button_3.visible = false
	button_4.visible = false
	
	button_5.visible = true
	button_6.visible =true
	button_7.visible = true
	camera_2d.enabled = true

func _on_button_7_pressed() -> void:
	button.visible = true
	button_2.visible = true
	button_3.visible = true
	button_4.visible = true
	
	button_5.visible = false
	button_6.visible = false
	button_7.visible = false
	camera_2d.enabled = false


func _on_button_5_pressed(index):
	if index == skin_manager.skins.size():
		index = 0
	skin_manager.selected_skin_index = index+1
	# Emit a signal for the player to listen to, if needed
	emit_signal("skin_changed")


func _on_button_6_pressed() -> void:
	pass # Replace with function body.
