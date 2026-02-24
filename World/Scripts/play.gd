extends Control

#region buttons

var playbutton = false
var lv1button = false
var lv2button = false
var lv3button = false
var lv4button = false
var lv5button = false
var quitbutton = false

#endregion

#region variables

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
@onready var animsprite: AnimatedSprite2D = $Echo

@onready var level_selection: Node2D = $"Level Selection"
@onready var skin_selection: Node2D = $"Skin Selection"
@onready var main_menu: Node2D = $"Main Menu"

#endregion

#region main

func _ready():
	skin_selection.visible = false
	level_selection.visible = false
	camera_skin.enabled = false
	camera_level.enabled = false

func _process(delta):
	if playbutton == true or quitbutton == true or lv1button == true or lv2button == true or lv3button == true or lv4button == true or lv5button == true:
		transitions.transition()

func _on_transition_transitioned() -> void:
	if playbutton == true:
		get_tree().change_scene_to_file("res://World/Scenes/Levels/Lv1.tscn")
	elif lv1button == true:
		get_tree().change_scene_to_file("res://World/Scenes/Levels/Lv1.tscn")
	elif lv2button == true:
		get_tree().change_scene_to_file("res://World/Scenes/Levels/Lv2.tscn")
	elif lv3button == true:
		get_tree().change_scene_to_file("res://World/Scenes/Levels/Lv3.tscn")
	elif lv4button == true:
		get_tree().change_scene_to_file("res://World/Scenes/Levels/Lv4.tscn")
	elif lv5button == true:
		get_tree().change_scene_to_file("res://World/Scenes/Levels/Lv5.tscn")
	elif quitbutton == true:
		get_tree().quit()

#endregion

#region buttons

func _on_button_pressed() -> void:
	playbutton = true


func _on_button_2_pressed() -> void:
	level_selection.visible = true
	camera_level.enabled = true
	main_menu.visible = false
	


func _on_button_3_pressed() -> void:
	quitbutton = true


func _on_button_4_pressed() -> void:
	main_menu.visible = false
	skin_selection.visible = true
	camera_skin.enabled = true

func _on_button_7_pressed() -> void:
	main_menu.visible = true
	skin_selection.visible = false
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
	level_selection.visible = false
	camera_level.enabled = false
	main_menu.visible = true


func _on_level_1_pressed() -> void:
	lv1button = true


func _on_level_2_pressed() -> void:
	lv2button = true


func _on_level_3_pressed() -> void:
	lv3button = true


func _on_level_4_pressed() -> void:
	lv4button = true


func _on_level_5_pressed() -> void:
	lv5button = true

#endregion
