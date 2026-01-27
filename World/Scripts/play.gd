extends Control

var playbutton = false
var quitbutton = false
@onready var skin_manager = get_node("/root/Skins")
@onready var transitions: CanvasLayer = $Transition
@onready var camera_skin: Camera2D = $"Skin Selection/Camera2D"
@onready var camera_level: Camera2D = $"Level Selection/Camera2D"
@onready var button_play: Button = $"Main Menu/Play"
@onready var button_level: Button =$"Main Menu/Levels"
@onready var button_quit: Button = $"Main Menu/Quit"
@onready var button_skin: Button = $"Main Menu/Skins"
@onready var button_skinback: Button =$"Skin Selection/Back"
@onready var button_skinright: Button = $"Skin Selection/Right"
@onready var button_skinleft: Button = $"Skin Selection/Left"
@onready var button_levelback: Button = $"Level Selection/Back"
@onready var animsprite: AnimatedSprite2D = $AnimatedSprite2D2

func _ready():
	camera_skin.enabled = false
	camera_level.enabled = false

func _process(delta):
	if playbutton == true or quitbutton == true:
		transitions.transition()

func _on_button_pressed() -> void:
	playbutton = true


func _on_button_2_pressed() -> void:
	camera_level.enabled = true
	button_levelback.visible = true


func _on_button_3_pressed() -> void:
	quitbutton = true


func _on_transition_transitioned() -> void:
	if playbutton == true:
		get_tree().change_scene_to_file("res://World/Scenes/Levels/Level_1.tscn")
	elif quitbutton == true:
		get_tree().quit()


func _on_button_4_pressed() -> void:
	button_play.visible = false
	button_level.visible = false
	button_quit.visible = false
	button_skin.visible = false
	
	button_skinright.visible = true
	button_skinleft.visible =true
	button_skinback.visible = true
	camera_skin.enabled = true

func _on_button_7_pressed() -> void:
	button_play.visible = true
	button_level.visible = true
	button_quit.visible = true
	button_skin.visible = true
	
	button_skinright.visible = false
	button_skinleft.visible = false
	button_skinback.visible = false
	camera_skin.enabled = false


func _on_button_5_pressed() -> void:
	var index = skin_manager.selected_skin_index+1
	if index == skin_manager.skins.size():
		index = 0
	skin_manager.selected_skin_index = index
	animsprite.modulate = skin_manager.skins[skin_manager.selected_skin_index-1]


func _on_button_6_pressed() -> void:
	var index = skin_manager.selected_skin_index
	if index == 0:
		index = skin_manager.skins.size()
	skin_manager.selected_skin_index = index-1
	animsprite.modulate = skin_manager.skins[skin_manager.selected_skin_index-1]


func _on_back_pressed() -> void:
	camera_level.enabled = false
	button_levelback.visible = false
