extends Control

const Level1 = preload("res://World/Scenes/Level_1.tscn")
var playbutton = false
var quitbutton = false
@onready var transitions: CanvasLayer = $Transition

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
