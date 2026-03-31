extends Node2D

@onready var show_time: Label = $ShowTime
@onready var lv_1: Label = $ShowTimeLv1
@onready var lv_2: Label = $ShowTimeLv2
@onready var lv_3: Label = $ShowTimeLv3
@onready var lv_4: Label = $ShowTimeLv4
@onready var lv_5: Label = $ShowTimeLv5


var kilenc: float = 0.0

func _ready() -> void:
	Stopwatch.stop()
	Stopwatch.stopwatch.text = ""
	for i in range(Stopwatch.times.size()):
		kilenc += Stopwatch.times[i]
		print(Stopwatch.times[i])
	show_time.text = Stopwatch.format_time(kilenc)
	if Stopwatch.times.size() > 3:
		lv_1.text = Stopwatch.format_time(Stopwatch.times[0])
		lv_2.text = Stopwatch.format_time(Stopwatch.times[1])
		lv_3.text = Stopwatch.format_time(Stopwatch.times[2])
		lv_4.text = Stopwatch.format_time(Stopwatch.times[3])
		lv_5.text = Stopwatch.format_time(Stopwatch.times[4])


func _on_main_menu_pressed() -> void:
	get_tree().change_scene_to_file("res://World/Scenes/HUD/Menu.tscn")
